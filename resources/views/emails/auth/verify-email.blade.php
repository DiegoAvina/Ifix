<x-mail::message>
# Confirma tu correo

Solo falta confirmar que esta dirección es tuya para terminar de activar tu cuenta en IFIX.

<x-mail::button :url="$url">
Confirmar correo
</x-mail::button>

Si no creaste una cuenta, ignora este correo.

<x-slot:subcopy>
Si el botón no funciona, copia y pega este enlace en tu navegador: <span class="break-all">[{{ $url }}]({{ $url }})</span>
</x-slot:subcopy>
</x-mail::message>
