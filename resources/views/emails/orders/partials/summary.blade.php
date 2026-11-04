@if ($additionalContent)
<x-mail::panel>
{{ $additionalContent }}
</x-mail::panel>

@endif
## Pedido {{ $summary->reference ?: '#'.$summary->id }}

<x-mail::table>
| Producto | Cant. | Total |
|:---------|:-----:|------:|
@foreach ($summary->lines as $line)
| {{ str_replace('|', '\|', $line->description) }} | {{ $line->quantity }} | {{ $line->total }} |
@endforeach
| Subtotal | | {{ $summary->subtotal }} |
| Envío | | {{ $summary->shipping }} |
| Impuestos | | {{ $summary->tax }} |
| **Total** | | **{{ $summary->total }}** |
</x-mail::table>

@if ($summary->shippingAddress)
**Dirección de envío**<br>
{{ $summary->shippingAddress->firstName }} {{ $summary->shippingAddress->lastName }}<br>
{{ $summary->shippingAddress->lineOne }}@if ($summary->shippingAddress->lineTwo), {{ $summary->shippingAddress->lineTwo }}@endif<br>
{{ $summary->shippingAddress->postcode }} {{ $summary->shippingAddress->city }}@if ($summary->shippingAddress->state), {{ $summary->shippingAddress->state }}@endif<br>
{{ $summary->shippingAddress->countryName }}

@endif
