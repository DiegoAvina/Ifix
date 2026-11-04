<x-mail::message>
# Restablece tu contraseña

Recibimos una solicitud para cambiar la contraseña de tu cuenta.

<x-mail::button :url="$url">
Cambiar contraseña
</x-mail::button>

Este enlace vence en {{ $minutes }} minutos. Si no lo solicitaste, ignora este correo: tu contraseña no cambiará.

<x-slot:subcopy>
Si el botón no funciona, copia y pega este enlace en tu navegador: <span class="break-all">[{{ $url }}]({{ $url }})</span>
</x-slot:subcopy>
</x-mail::message>
