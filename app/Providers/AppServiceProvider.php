<?php

namespace App\Providers;

use App\Filament\Resources\HeroSlideResource;
use App\Filament\Resources\LegalPageResource;
use App\Http\Middleware\SetPanelLocale;
use App\PaymentTypes\MercadoPagoPayment;
use App\Services\Lunar\SafeTelemetryService;
use App\Services\Shipping\StandardShippingModifier;
use App\Services\Storefront\CategoryService;
use App\Services\Storefront\Search\FuzzyProductMatcher;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\Support\Colors\Color;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Lunar\Base\TelemetryServiceInterface;
use Lunar\Base\ShippingModifiers;
use Lunar\Facades\Payments;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;
//panel
use Lunar\Admin\Support\Facades\LunarPanel;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        LunarPanel::panel(fn (Panel $panel) => $panel
            ->resources([
                HeroSlideResource::class,
                LegalPageResource::class,
                \App\Filament\Resources\ShippingZoneResource::class,
                \App\Filament\Resources\ShippingExtendedPostcodeResource::class,
            ])
            ->pages([
                \App\Filament\Pages\ShippingSettingsPage::class,
            ])
            // Marca IFIX en lugar de la de Lunar (licencia MIT: se permite).
            ->brandName('IFIX')
            ->brandLogo(fn () => asset('images/ifix-logo-dark.svg'))
            ->darkModeBrandLogo(fn () => asset('images/ifix-logo.svg'))
            ->brandLogoHeight('2.25rem')
            ->favicon(fn () => asset('favicon.svg'))
            ->colors(['primary' => Color::Amber])
            // Español en todo el panel. Persistente: también aplica a las
            // peticiones internas de Livewire (filtros, formularios, modales).
            ->middleware([SetPanelLocale::class], isPersistent: true)
            // Orden del menú con los nombres traducidos de Lunar.
            ->navigationGroups([
                'Tienda en línea',
                'Catálogo',
                'Ventas',
                NavigationGroup::make()->label('Configuraciones')->collapsed(),
            ])
        )
            // Botón "Aplicar envío gratis" en el detalle del pedido.
            ->extensions([
                \Lunar\Admin\Filament\Resources\OrderResource\Pages\ManageOrder::class => \App\Filament\Extensions\ManageOrderExtension::class,
            ])
            ->register();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app->singleton(TelemetryServiceInterface::class, SafeTelemetryService::class);

        $this->app->make(ShippingModifiers::class)
            ->add(StandardShippingModifier::class);

        // Driver de las Transactions de Mercado Pago; sin él, abrir en el
        // panel un pedido pagado lanza "Driver [mercadopago] not supported".
        Payments::extend(MercadoPagoPayment::DRIVER, fn ($app) => $app->make(MercadoPagoPayment::class));

        $this->configureRateLimiting();

        $this->configureAuthEmails();

        // Detrás de un túnel o proxy HTTPS la petición llega como http; sin
        // esto los enlaces (y las URLs que recibe Mercado Pago) saldrían en http.
        // Solo para el host de APP_URL: en http://127.0.0.1:8000 forzarlo manda
        // al navegador a https://127.0.0.1:8000, que `artisan serve` no atiende.
        $appUrl = (string) config('app.url');

        if (str_starts_with($appUrl, 'https://')
            && ($this->app->runningInConsole() || request()->getHost() === parse_url($appUrl, PHP_URL_HOST))) {
            URL::forceScheme('https');
        }

        // rescue: un fallo al leer las categorías no debe tumbar cada página
        // (el navbar también se usa en las páginas de error).
        View::composer('partials.navbar', fn ($view) => $view->with(
            'storefrontCategories',
            rescue(fn () => app(CategoryService::class)->all(), [], false),
        ));

        // Monto de envío gratis de "Ajustes de envío" (null = desactivado).
        View::composer(['partials.navbar', 'cart.index', 'products.partials.shipping'], fn ($view) => $view->with(
            'freeShippingFrom',
            rescue(fn () => app(\App\Services\Shipping\ShippingViewService::class)->freeShippingFrom(), null, false),
        ));

        // Días de entrega en la ficha de producto: zona del C.P. que el cliente
        // haya escrito (o la zona general si aún no lo escribe).
        View::composer('products.partials.shipping', fn ($view) => $view->with(
            'shippingEstimate',
            rescue(fn () => app(\App\Services\Shipping\ShippingViewService::class)->estimateFor(cartTotal: 0), null, false),
        ));

        // El índice de búsqueda aproximada se reconstruye al editar productos.
        foreach ([Product::class, ProductVariant::class] as $model) {
            $model::saved(fn () => FuzzyProductMatcher::flush());
            $model::deleted(fn () => FuzzyProductMatcher::flush());
        }
    }

    /**
     * Correos de autenticación con las vistas de la tienda, en español,
     * en lugar de las plantillas por defecto de Laravel.
     */
    protected function configureAuthEmails(): void
    {
        ResetPassword::toMailUsing(fn ($user, string $token) => (new MailMessage)
            ->subject('Restablece tu contraseña de IFIX')
            ->markdown('emails.auth.reset-password', [
                'url' => route('password.reset', [
                    'token' => $token,
                    'email' => $user->getEmailForPasswordReset(),
                ]),
                'minutes' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire'),
            ]));

        VerifyEmail::toMailUsing(fn ($user, string $url) => (new MailMessage)
            ->subject('Confirma tu correo en IFIX')
            ->markdown('emails.auth.verify-email', ['url' => $url]));
    }

    /**
     * Limiters nombrados para rutas que necesitan una clave distinta
     * a "IP simple" (throttle:N,1 directo en la ruta ya cubre eso).
     * register/forgot-password/reset-password usan ese atajo numérico
     * directamente en routes/auth.php — no se duplican aquí.
     */
    protected function configureRateLimiting(): void
    {
        // Capa GRUESA del webhook de Mercado Pago: solo protege contra
        // un flood bruto por IP (higiene básica de cualquier endpoint
        // público). La protección real contra el problema reportado
        // (llamadas repetidas a findPayment() vía replay de una firma
        // capturada) vive en PaymentWebhookService::MAX_LOOKUPS_PER_EVENT,
        // que limita por data_id y solo cuenta intentos con firma ya
        // válida — no depende de esta IP ni la sustituye.
        //
        // 60/min por IP es deliberadamente generoso: sin conocer el
        // patrón real de tráfico de Mercado Pago (reintentos propios,
        // varios eventos en paralelo desde su pool de servidores) no
        // hay una cifra "correcta" que inventar; esto solo evita un
        // flood evidente, sin arriesgar bloquear notificaciones
        // legítimas.
        RateLimiter::for('mercadopago-webhook', function (Request $request) {
            return Limit::perMinute(60)->by($request->ip());
        });

        // /checkout/place requiere sesión, así que el usuario
        // autenticado es una clave más correcta que la IP (varios
        // clientes legítimos pueden compartir IP en una oficina/NAT;
        // esto evita que se bloqueen entre sí). 10/min da margen de
        // sobra para reintentar tras un error de stock/dirección sin
        // acotar una compra legítima, pero cierra la puerta a scripts
        // creando muchas Orders por minuto.
        RateLimiter::for('checkout-place', function (Request $request) {
            return Limit::perMinute(10)->by(
                $request->user()?->id ?: $request->ip()
            );
        });

        // /api/search/suggestions no la usa hoy ningún elemento activo
        // del frontend (el buscador del navbar es un <form> GET normal
        // hacia el catálogo, no llama este endpoint) — sigue siendo un
        // endpoint público sin autenticación, así que se limita igual.
        // La barra de búsqueda lo consulta mientras se escribe (con
        // debounce); 60/min por IP cubre varias búsquedas seguidas sin
        // abrir la puerta a scraping sistemático del catálogo.
        RateLimiter::for('search-suggestions', function (Request $request) {
            return Limit::perMinute(60)->by($request->ip());
        });

        // Acciones que escriben en el carrito o la sesión (agregar,
        // cambiar cantidad, código postal) son públicas y crean filas
        // por visitante. 60/min cubre de sobra a una persona dando
        // clics, y frena scripts que inflan la base de datos.
        RateLimiter::for('cart-actions', function (Request $request) {
            return Limit::perMinute(60)->by(
                $request->user()?->id ?: $request->ip()
            );
        });
    }
}
