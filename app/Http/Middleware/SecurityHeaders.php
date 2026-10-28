<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cabeceras de seguridad aplicadas a TODA respuesta (storefront, checkout,
 * pagos y el panel /admin de Filament), registrada como middleware global
 * en bootstrap/app.php — no depende de que cada controlador las agregue.
 *
 * La CSP está calibrada específicamente contra lo que esta app carga hoy
 * (Tailwind compilado localmente, Google/Bunny Fonts, Alpine.js,
 * Livewire/Filament); no es una política genérica copiada de una
 * plantilla.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        // Nonce único por request para los scripts inline puntuales de
        // la app (tema y carrusel del hero), sin habilitar unsafe-inline.
        $nonce = base64_encode(Str::random(24));

        View::share('cspNonce', $nonce);

        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=(), usb=()');
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin-allow-popups');

        // No anunciar versión de PHP/servidor.
        $response->headers->remove('X-Powered-By');
        header_remove('X-Powered-By');
        $response->headers->set('Content-Security-Policy', $this->buildCsp($nonce));

        // HSTS solo tiene sentido (y solo lo honran los navegadores) en
        // una respuesta servida realmente por HTTPS. En `php artisan
        // serve` local esto nunca es true, así que nunca se envía en
        // desarrollo sin necesidad de comprobar el entorno aparte.
        if ($request->secure()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains'
            );
        }

        return $response;
    }

    protected function buildCsp(string $nonce): string
    {
        // Cuando `npm run dev` está corriendo, Laravel sirve CSS/JS
        // desde el servidor de Vite (otro origen, ej. http://[::1]:5173)
        // en vez de public/build — eso incluye su cliente de HMR y el
        // WebSocket que usa para recargar en caliente. Sin esto en la
        // CSP, el navegador bloquea TODO (JS y, por lo tanto, el CSS
        // que Vite inyecta vía JS en modo dev) y la página se ve sin
        // estilos. Solo se activa si el archivo `public/hot` existe
        // (lo crea/borra automáticamente `npm run dev`) Y estamos en
        // entorno local — nunca en producción, donde ese archivo no
        // debería existir de todas formas.
        $viteDevOrigin = app()->environment('local')
            ? $this->resolveViteDevServerOrigin()
            : null;

        $directives = [

            // Bloquea Flash/plugins; sin uso legítimo en esta app.
            "object-src 'none'",

            // Evita que un <base> inyectado redirija recursos relativos
            // a un dominio atacante.
            "base-uri 'self'",

            // Los <form> de la app solo envían a rutas propias (incluido
            // el de "Pagar con Mercado Pago": hace POST a nuestra propia
            // ruta, que redirige server-side al init_point; nunca se
            // envía un <form> directamente a un dominio de Mercado Pago).
            "form-action 'self'",

            // OBJETIVO PRINCIPAL de este hallazgo: nadie puede embeber
            // esta app en un <iframe>. Duplicado con X-Frame-Options
            // por compatibilidad con navegadores que no soporten CSP.
            "frame-ancestors 'none'",

            // La app no embebe iframes de terceros hoy; 'self' deja
            // margen por si el panel admin necesita alguno propio
            // (p.ej. previsualización de PDF) sin abrir la puerta a
            // dominios externos.
            "frame-src 'self'",

            // Scripts: propio dominio + nonce para los scripts inline
            // puntuales (tema y carrusel del hero) + 'unsafe-eval',
            // requerido específicamente por Alpine.js (usa
            // `new Function()` para evaluar expresiones de x-data/x-on
            // en tiempo de ejecución; Livewire empaqueta el mismo
            // Alpine). Migrar al build @alpinejs/csp eliminaría esta
            // necesidad, pero es un cambio de framework fuera de
            // alcance de este hallazgo. El Tailwind CDN YA NO está
            // aquí: Tailwind ahora se compila localmente vía Vite/
            // PostCSS (ver tailwind.config.js) y no queda ningún
            // dominio externo en script-src.
            "script-src 'self' 'nonce-{$nonce}' 'unsafe-eval'".($viteDevOrigin ? " {$viteDevOrigin}" : ''),

            // Estilos: 'unsafe-inline' es necesario porque Filament/
            // Livewire (vendor, no controlamos su HTML) generan atributos
            // style="..." inline en cada request para su grid dinámico,
            // y la propia app usa algún style="" puntual (gradientes
            // decorativos). No hay forma práctica de tokenizar eso con
            // nonces sin parchear el vendor. Fuentes de Google/Bunny.
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://fonts.bunny.net".($viteDevOrigin ? " {$viteDevOrigin}" : ''),

            "font-src 'self' https://fonts.gstatic.com https://fonts.bunny.net",

            // Imágenes de producto / hero vienen de campos de texto
            // libre (URL) editables desde el panel admin, no de un
            // storage fijo; no hay una lista cerrada de dominios que
            // whitelistear hoy.
            "img-src 'self' https: data:",

            // El carrito/checkout solo hace fetch/XHR a rutas propias
            // (confirmado: no hay ninguna llamada Http:: saliente desde
            // el frontend). Nada externo necesita connect-src.
            "connect-src 'self'".($viteDevOrigin ? " {$viteDevOrigin} ".str_replace('http', 'ws', $viteDevOrigin) : ''),

            "default-src 'self'",
        ];

        return implode('; ', $directives);
    }

    /**
     * Lee `public/hot` (lo gestiona Vite, no esta app) para saber en
     * qué origen está sirviendo `npm run dev` ahora mismo. Devuelve
     * null si no existe el archivo (modo producción/build estático).
     */
    protected function resolveViteDevServerOrigin(): ?string
    {
        $hotFile = public_path('hot');

        if (! is_file($hotFile)) {
            return null;
        }

        $url = trim((string) file_get_contents($hotFile));

        if ($url === '') {
            return null;
        }

        $parts = parse_url($url);

        if (! $parts || ! isset($parts['host'])) {
            return null;
        }

        $scheme = $parts['scheme'] ?? 'http';
        $host = $parts['host'];
        $port = isset($parts['port']) ? ':'.$parts['port'] : '';

        return "{$scheme}://{$host}{$port}";
    }
}
