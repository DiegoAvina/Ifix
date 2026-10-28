<?php

namespace App\Support;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/**
 * Limpia HTML editado en el panel (descripciones de producto) antes de
 * imprimirlo sin escapar: conserva formato básico y elimina scripts,
 * atributos on*, javascript: y similares.
 */
class SafeHtml
{
    private static ?HtmlSanitizer $sanitizer = null;

    public static function clean(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        self::$sanitizer ??= new HtmlSanitizer(
            (new HtmlSanitizerConfig())
                ->allowSafeElements()
                ->allowLinkSchemes(['https', 'http', 'mailto'])
                ->allowRelativeLinks()
                ->allowMediaSchemes(['https', 'http'])
                ->allowRelativeMedias()
                ->forceAttribute('a', 'rel', 'noopener noreferrer')
        );

        return self::$sanitizer->sanitize($html);
    }

    /**
     * URL para un href: rutas internas, anclas o http(s). Cualquier otra
     * cosa (javascript:, data:, //otro-sitio) se reemplaza por el fallback.
     */
    public static function url(?string $url, string $fallback = '#'): string
    {
        $url = trim((string) $url);

        return preg_match('~^(/(?!/)|#|https?://)~i', $url) ? $url : $fallback;
    }
}
