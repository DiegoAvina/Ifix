<x-mail::message>
# {{ $needsReview ? 'Pedido pagado sin stock suficiente' : 'Nuevo pedido pagado' }}

@if ($needsReview)
Mercado Pago confirmó el pago, pero no hay stock para surtir todo el pedido. Revisa si se puede surtir o si hay que reembolsar.
@else
Mercado Pago confirmó el pago. El pedido está listo para prepararse.
@endif

<x-mail::button :url="$orderUrl">
Abrir en Lunar
</x-mail::button>

**Cliente:** {{ $summary->shippingAddress ? trim($summary->shippingAddress->firstName.' '.$summary->shippingAddress->lastName) : '—' }}<br>
**Correo:** {{ $customerEmail ?? '—' }}<br>
**Teléfono:** {{ $customerPhone ?? '—' }}

@include('emails.orders.partials.summary', ['additionalContent' => null])
</x-mail::message>
