<?php

use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // El webhook de Mercado Pago lo llama su servidor, no un
        // navegador con sesión/token CSRF de Laravel. La autenticidad
        // de esa ruta se garantiza verificando su firma (x-signature),
        // no con CSRF.
        $middleware->validateCsrfTokens(except: [
            'payments/mercadopago/webhook',
        ]);

        // Global: se aplica a toda respuesta, incluido el panel /admin
        // de Filament (que registra su propio stack de middleware por
        // panel, pero sigue pasando por el stack global del Kernel).
        $middleware->append(SecurityHeaders::class);

        // El túnel (cloudflared) o el proxy del hosting reciben HTTPS y lo
        // reenvían a Laravel por HTTP. Sin confiar en sus cabeceras
        // X-Forwarded-*, Laravel cree que la petición es http:// y los
        // enlaces firmados (verificación de correo) dan "Invalid signature".
        // Solo se confía en proxies conocidos (por defecto, la propia máquina).
        // Solo se aceptan peticiones dirigidas al dominio de APP_URL (y sus
        // subdominios, p. ej. www). Sin esto, alguien puede mandar otro Host
        // y los correos de recuperar contraseña o verificación apuntarían a
        // su sitio. Laravel no lo aplica en APP_ENV=local ni en las pruebas.
        $middleware->trustHosts();

        $middleware->trustProxies(
            at: array_filter(array_map('trim', explode(',', (string) env('TRUSTED_PROXIES', '127.0.0.1,::1')))),
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // JSON para la API y para las peticiones que lo piden (el carrito
        // por JavaScript): así reciben el mensaje de error (p. ej. 422 de
        // validación) en vez de una redirección HTML que no pueden leer.
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
