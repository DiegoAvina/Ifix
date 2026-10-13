<?php

namespace App\Providers;

use App\Services\Shipping\StandardShippingModifier;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Lunar\Base\ShippingModifiers;
//panel
use Lunar\Admin\Support\Facades\LunarPanel;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        LunarPanel::register();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app->make(ShippingModifiers::class)
            ->add(StandardShippingModifier::class);

        $this->configureRateLimiting();
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
        // 30/min por IP deja margen si en el futuro se conecta a un
        // input con debounce típico (200-300ms) sin abrir la puerta a
        // scraping sistemático del catálogo.
        RateLimiter::for('search-suggestions', function (Request $request) {
            return Limit::perMinute(30)->by($request->ip());
        });
    }
}
