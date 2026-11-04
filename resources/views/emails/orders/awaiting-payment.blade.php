<x-mail::message>
# Tu pedido está pendiente de pago

Ya registramos tu pedido. En cuanto se confirme el pago, empezamos a prepararlo.

<x-mail::button :url="$orderUrl">
Ver mi pedido
</x-mail::button>

@include('emails.orders.partials.summary')

¿Necesitas ayuda? Responde a este correo y con gusto te atendemos.
</x-mail::message>
