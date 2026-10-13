import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    server: {
        // Por defecto Vite se liga a `::1` (IPv6), y `public/hot` queda
        // como `http://[::1]:5173`. La sintaxis de Content-Security-Policy
        // no soporta direcciones IPv6 entre corchetes como fuente válida
        // (el navegador la descarta silenciosamente, sin ese match el CSS/
        // JS del dev server queda bloqueado) — forzar IPv4 evita el problema
        // de raíz en vez de intentar expresar [::1] de alguna forma válida
        // en la CSP, que no existe.
        host: '127.0.0.1',
    },
});
