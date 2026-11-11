<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pone el panel de administración (/lunar) en español. Solo se registra en
 * el panel: la tienda conserva su configuración de idioma.
 */
class SetPanelLocale
{
    public const LOCALE = 'es';

    public function handle(Request $request, Closure $next): Response
    {
        App::setLocale(self::LOCALE);
        Carbon::setLocale(self::LOCALE);

        return $next($request);
    }
}
