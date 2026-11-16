# Pendientes

Marca cada casilla (`[x]`) al terminarla.

---

## SEO: que Google encuentre a IFIX (búsquedas "ifix detallado", etc.)

> El código ya está listo (schema de marca, H1, títulos, sitemap, robots). Lo que falta es configuración y altas en herramientas externas. Detalles en [config/seo.php](../config/seo.php).

### 1. `.env` del sitio público (hacer primero) ⚠️

Mientras esto no esté, `robots.txt` responde `Disallow: /` y **Google no indexa nada**.

- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false` (también es seguridad: con `true` los errores se muestran a cualquier visitante)
- [ ] `APP_URL=https://tudominio.com` (dominio real, con https, sin `/` al final; hoy está `http://127.0.0.1:8000`)
- [ ] Ejecutar `php artisan config:clear`
- [ ] Comprobar `https://tudominio.com/robots.txt`: debe listar `Disallow: /carrito`, etc. y terminar con `Sitemap: https://tudominio.com/sitemap.xml`
- [ ] Comprobar que `https://tudominio.com/sitemap.xml` abre y muestra URLs con el dominio real
- [ ] Decidir si la tienda pública seguirá corriendo desde la PC de desarrollo (túnel Cloudflare) o desde otro servidor

### 2. Google Search Console

- [ ] Entrar a https://search.google.com/search-console con la cuenta de Google del negocio
- [ ] Agregar propiedad → "Prefijo de URL" → `https://tudominio.com`
- [ ] Método "Etiqueta HTML": copiar solo el valor de `content="..."` en `GOOGLE_SITE_VERIFICATION=` del `.env`
- [ ] `php artisan config:clear` y pulsar "Verificar"
- [ ] Sitemaps → enviar `sitemap.xml`
- [ ] Inspección de URLs → "Solicitar indexación" de la home y 3–4 productos principales
- [ ] Revisar en 2–4 semanas: Rendimiento (búsquedas con las que aparecemos) y Páginas (errores de indexación)

### 3. Bing Webmaster Tools (también alimenta DuckDuckGo, Yahoo y ChatGPT Search)

- [ ] Entrar a https://www.bing.com/webmasters
- [ ] "Importar desde Google Search Console" (no requiere código)
- [ ] Solo si se verifica manualmente: valor de `msvalidate.01` en `BING_SITE_VERIFICATION=`

### 4. Redes sociales oficiales (`SEO_SAME_AS`)

Ayuda a que aparezca el panel de marca de IFIX en Google.

- [ ] Juntar las URLs oficiales: Facebook, Instagram, TikTok, YouTube, Mercado Libre, etc.
- [ ] Ponerlas en el `.env`, en una línea, separadas por coma y sin espacios:
      `SEO_SAME_AS=https://facebook.com/...,https://instagram.com/...`
- [ ] En cada red, poner el enlace a la tienda en la bio / "sitio web"

### 5. Google Business Profile

- [ ] Crear perfil en https://business.google.com con el nombre **IFIX Detallado Automotriz**
- [ ] Categoría: tienda de accesorios / productos para el cuidado de automóviles
- [ ] Sin local abierto al público → ocultar dirección y poner área de servicio: México
- [ ] Sitio web, teléfono +52 55 4169 8230, horario, logo y fotos de productos
- [ ] Completar la verificación que pida Google

### 6. Continuo (mejora con el tiempo)

- [ ] Pedir reseñas en Google Business a clientes satisfechos
- [ ] Conseguir enlaces hacia la tienda: distribuidores, talleres de detallado, blogs y grupos de autos
- [ ] Usar siempre el mismo nombre, "IFIX Detallado Automotriz", en redes, marketplaces y empaques
- [ ] Descripciones propias (no copiadas) en cada producto
- [ ] Escribir los datos para una sección visible "Sobre IFIX" en la home (qué es la marca, origen, qué la distingue) y pasárselos a Claude para maquetarla

---

## Vista de producto en móvil

- [ ] Probar en un teléfono real (o DevTools a 375 px) con un producto de descripción larga y varias imágenes: galería con swipe, lightbox y botón "atrás", barra de compra fija, "Ver descripción completa" y "Ver todas" en especificaciones
- [ ] Mandar capturas para una revisión de diseño visual (jerarquía, contraste, ritmo)
