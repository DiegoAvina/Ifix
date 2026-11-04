<x-mail::message>
# ¡Gracias por tu compra!

Recibimos tu pago y ya estamos preparando tu pedido. Te avisaremos cuando salga a entrega.

<x-mail::button :url="$orderUrl">
Ver mi pedido
</x-mail::button>

@include('emails.orders.partials.summary')

¿Necesitas ayuda? Responde a este correo y con gusto te atendemos.
</x-mail::message>
