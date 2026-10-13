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
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
