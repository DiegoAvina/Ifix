<x-mail::message>
# Tu pedido va en camino

Ya enviamos tu pedido a la dirección que nos indicaste.

<x-mail::button :url="$orderUrl">
Ver mi pedido
</x-mail::button>

@include('emails.orders.partials.summary')

¿Necesitas ayuda? Responde a este correo y con gusto te atendemos.
</x-mail::message>
