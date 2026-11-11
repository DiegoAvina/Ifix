<x-mail::message>
# ¡Tu pedido ahora tiene envío gratis!

Le aplicamos **envío gratis** a tu pedido **#{{ $summary->reference ?: $summary->id }}**. Ya no pagas el envío: tu nuevo total es **{{ $summary->total }}**.

Tu pedido sigue pendiente de pago. Pulsa el botón para pagarlo con el nuevo total.

<x-mail::button :url="$orderUrl">
Pagar mi pedido
</x-mail::button>

@include('emails.orders.partials.summary')

Si ya habías abierto la página de pago antes de este correo, vuelve a entrar desde el botón para ver el total actualizado.

¿Necesitas ayuda? Responde a este correo y con gusto te atendemos.
</x-mail::message>
