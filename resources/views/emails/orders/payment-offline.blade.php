<x-mail::message>
# Estamos confirmando tu pago

Registramos tu pago y lo estamos verificando. Te avisaremos en cuanto quede confirmado.

<x-mail::button :url="$orderUrl">
Ver mi pedido
</x-mail::button>

@include('emails.orders.partials.summary')

¿Necesitas ayuda? Responde a este correo y con gusto te atendemos.
</x-mail::message>
