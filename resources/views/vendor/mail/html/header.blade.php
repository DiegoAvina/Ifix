@props(['url'])
{{--
    Encabezado de todos los correos: logo de IFIX en PNG (Gmail y Outlook no
    muestran SVG). La imagen mide el doble para verse nítida en pantallas de
    alta densidad; la placa oscura la hace legible en modo claro y oscuro.
    La URL es absoluta (APP_URL): el correo se lee fuera del sitio.
--}}
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
<img src="{{ rtrim(config('app.url'), '/') }}/images/ifix-logo-email.png" width="222" height="86" alt="IFIX — Productos para detallado" style="display: block; width: 222px; max-width: 222px; height: auto; border: 0;">
</a>
</td>
</tr>
