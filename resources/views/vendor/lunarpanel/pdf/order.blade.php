{{--
    PDF del pedido que descarga el panel de Lunar ("Descargar PDF").
    Reemplaza a lunarpanel::pdf.order. Lo dibuja DomPDF: solo tablas y CSS
    básico (sin flex ni grid) y la fuente DejaVu Sans para los acentos.

    No es un CFDI: en México la factura fiscal la timbra el SAT; este
    documento es un comprobante de la compra y así se aclara al pie.
--}}
@php
    $tz = 'America/Mexico_City';
    $date = ($record->placed_at ?? $record->created_at)?->timezone($tz)->locale('es');
    $statusLabel = config("lunar.orders.statuses.{$record->status}.label", $record->status);
    $statusColor = config("lunar.orders.statuses.{$record->status}.color", '#52525b');

    $billing = $record->billingAddress;
    $shipping = $record->shippingAddress;

    $payment = $record->transactions
        ->where('success', true)
        ->sortByDesc('id')
        ->first() ?? $record->transactions->sortByDesc('id')->first();

    $paymentStatus = [
        'approved' => 'Aprobado',
        'pending' => 'Pendiente',
        'in_process' => 'En revisión',
        'rejected' => 'Rechazado',
        'refunded' => 'Reembolsado',
        'cancelled' => 'Cancelado',
    ];

    $logo = public_path('images/ifix-logo-email.png');
    $logoData = is_file($logo) ? 'data:image/png;base64,'.base64_encode(file_get_contents($logo)) : null;
    $site = parse_url(config('app.url'), PHP_URL_HOST);
    $contactEmail = config('mail.from.address');

    $lines = $record->productLines;
    $hasDiscount = $record->discount_total->value > 0;
@endphp
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Pedido {{ $record->reference }} | IFIX</title>
    <style>
        @page { margin: 0; }
        * { font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif; }
        body { margin: 0; color: #27272a; font-size: 10px; line-height: 1.45; }
        table { border-collapse: collapse; width: 100%; }
        td, th { vertical-align: top; }

        .page { padding: 0 40px 120px; }

        /* Banda superior */
        .band { background: #0b0c0e; color: #fff; padding: 26px 40px 22px; }
        .band td { vertical-align: middle; }
        .doc-title { color: #f5a623; font-size: 9px; font-weight: bold; letter-spacing: 2px; text-transform: uppercase; }
        .doc-ref { font-size: 22px; font-weight: bold; color: #fff; margin-top: 2px; }
        .accent { height: 4px; background: #f5a623; }

        /* Datos del documento */
        .meta { margin-top: 22px; }
        .meta td { padding: 0; }
        .label { color: #71717a; font-size: 7.5px; font-weight: bold; letter-spacing: 1.2px; text-transform: uppercase; }
        .value { font-size: 10.5px; font-weight: bold; color: #18181b; margin-top: 2px; }
        .status { display: inline-block; padding: 3px 9px; border-radius: 10px; color: #fff; font-size: 8.5px; font-weight: bold; margin-top: 3px; }

        /* Direcciones */
        .parties { margin-top: 20px; }
        .box { border: 1px solid #e4e4e7; border-radius: 6px; padding: 12px 14px; }
        .box-title { color: #b45309; font-size: 7.5px; font-weight: bold; letter-spacing: 1.2px; text-transform: uppercase; margin-bottom: 6px; }
        .box strong { font-size: 10.5px; color: #18181b; }
        .muted { color: #71717a; }

        /* Conceptos */
        .items { margin-top: 22px; }
        .items th { background: #f4f4f5; color: #52525b; font-size: 7.5px; font-weight: bold; letter-spacing: 1px; text-transform: uppercase; padding: 8px 10px; text-align: left; border-bottom: 2px solid #18181b; }
        .items td { padding: 9px 10px; border-bottom: 1px solid #e4e4e7; }
        .items .num { text-align: right; white-space: nowrap; }
        .items .center { text-align: center; }
        .item-name { font-weight: bold; color: #18181b; font-size: 10px; }
        .item-sub { color: #71717a; font-size: 8.5px; margin-top: 1px; }

        /* Totales */
        .summary { margin-top: 16px; }
        .totals td { padding: 5px 10px; }
        .totals .amount { text-align: right; white-space: nowrap; }
        .totals .grand td { background: #0b0c0e; color: #fff; font-size: 12px; font-weight: bold; padding: 10px; }
        .totals .grand .amount { color: #f5a623; }
        .discount { color: #15803d; }

        /* Pago */
        .pay-box { background: #fafaf9; border: 1px solid #e4e4e7; border-radius: 6px; padding: 12px 14px; }

        .notes { margin-top: 18px; border-left: 3px solid #f5a623; padding: 8px 12px; background: #fffbeb; }

        /* Pie fijo en cada página */
        .footer { position: fixed; bottom: 0; left: 0; right: 0; padding: 14px 40px 18px; border-top: 1px solid #e4e4e7; font-size: 8px; color: #71717a; }
        .footer strong { color: #18181b; }
    </style>
</head>
<body>

    <div class="band">
        <table>
            <tr>
                <td width="55%">
                    @if ($logoData)
                        <img src="{{ $logoData }}" alt="IFIX" style="width: 150px; height: auto;">
                    @else
                        <span style="font-size: 26px; font-weight: bold; font-style: italic;">IFIX</span>
                    @endif
                </td>
                <td width="45%" style="text-align: right;">
                    <div class="doc-title">Comprobante de pedido</div>
                    <div class="doc-ref">#{{ $record->reference }}</div>
                </td>
            </tr>
        </table>
    </div>
    <div class="accent"></div>

    <div class="page">

        {{-- Datos del documento --}}
        <table class="meta">
            <tr>
                <td width="25%">
                    <div class="label">Fecha</div>
                    <div class="value">{{ $date?->translatedFormat('d M Y') }}</div>
                    <div class="muted">{{ $date?->format('H:i') }} h (CDMX)</div>
                </td>
                <td width="25%">
                    <div class="label">Estado</div>
                    <span class="status" style="background: {{ $statusColor }};">{{ $statusLabel }}</span>
                </td>
                <td width="25%">
                    <div class="label">Forma de pago</div>
                    <div class="value">{{ $payment ? 'Mercado Pago' : 'Pendiente de pago' }}</div>
                </td>
                <td width="25%" style="text-align: right;">
                    <div class="label">Emitido por</div>
                    <div class="value">IFIX</div>
                    <div class="muted">{{ $site }}</div>
                </td>
            </tr>
        </table>

        {{-- Cliente y envío --}}
        <table class="parties">
            <tr>
                <td width="49%">
                    <div class="box">
                        <div class="box-title">Cliente</div>
                        @if ($billing)
                            <strong>{{ $billing->fullName }}</strong><br>
                            @if ($billing->company_name){{ $billing->company_name }}<br>@endif
                            @if ($billing->tax_identifier)RFC: {{ $billing->tax_identifier }}<br>@endif
                            {{ $billing->line_one }}@if ($billing->line_two), {{ $billing->line_two }}@endif<br>
                            @if ($billing->line_three){{ $billing->line_three }}<br>@endif
                            {{ $billing->city }}, {{ $billing->state }} C.P. {{ $billing->postcode }}<br>
                            {{ $billing->country?->name }}<br>
                            @if ($billing->contact_email)<span class="muted">{{ $billing->contact_email }}</span><br>@endif
                            @if ($billing->contact_phone)<span class="muted">Tel. {{ $billing->contact_phone }}</span>@endif
                        @else
                            <span class="muted">{{ $record->user?->email ?? 'Sin datos del cliente' }}</span>
                        @endif
                    </div>
                </td>
                <td width="2%"></td>
                <td width="49%">
                    <div class="box">
                        <div class="box-title">Enviar a</div>
                        @if ($shipping)
                            <strong>{{ $shipping->fullName }}</strong><br>
                            @if ($shipping->company_name){{ $shipping->company_name }}<br>@endif
                            {{ $shipping->line_one }}@if ($shipping->line_two), {{ $shipping->line_two }}@endif<br>
                            @if ($shipping->line_three){{ $shipping->line_three }}<br>@endif
                            {{ $shipping->city }}, {{ $shipping->state }} C.P. {{ $shipping->postcode }}<br>
                            {{ $shipping->country?->name }}<br>
                            @if ($shipping->contact_phone)<span class="muted">Tel. {{ $shipping->contact_phone }}</span><br>@endif
                            @if ($shipping->delivery_instructions)<span class="muted">Indicaciones: {{ $shipping->delivery_instructions }}</span>@endif
                        @else
                            <span class="muted">Sin dirección de envío</span>
                        @endif
                    </div>
                </td>
            </tr>
        </table>

        {{-- Conceptos --}}
        <table class="items">
            <thead>
                <tr>
                    <th width="5%" class="center">#</th>
                    <th width="{{ $hasDiscount ? '41%' : '53%' }}">Producto</th>
                    <th width="8%" class="center">Cant.</th>
                    <th width="16%" class="num">Precio unit.</th>
                    @if ($hasDiscount)<th width="12%" class="num">Descuento</th>@endif
                    <th width="18%" class="num">Importe</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($lines as $line)
                    <tr>
                        <td class="center muted">{{ $loop->iteration }}</td>
                        <td>
                            <div class="item-name">{{ $line->description }}</div>
                            <div class="item-sub">
                                @if ($line->option){{ $line->option }} · @endif SKU {{ $line->identifier }}
                            </div>
                        </td>
                        <td class="center">{{ $line->quantity }}</td>
                        <td class="num">{{ $line->unit_price->formatted }}</td>
                        @if ($hasDiscount)<td class="num discount">{{ $line->discount_total->value ? '-'.$line->discount_total->formatted : '—' }}</td>@endif
                        <td class="num"><strong>{{ $line->sub_total->formatted }}</strong></td>
                    </tr>
                @empty
                    <tr><td colspan="{{ $hasDiscount ? 6 : 5 }}" class="muted center">Sin productos</td></tr>
                @endforelse
            </tbody>
        </table>

        {{-- Pago y totales --}}
        <table class="summary">
            <tr>
                <td width="52%">
                    <div class="pay-box">
                        <div class="box-title">Información de pago</div>
                        @if ($payment)
                            <strong>Mercado Pago</strong><br>
                            Operación: {{ $payment->reference }}<br>
                            Estado: {{ $paymentStatus[$payment->status] ?? $payment->status }}<br>
                            <span class="muted">{{ $payment->created_at?->timezone($tz)->locale('es')->translatedFormat('d M Y, H:i') }} h</span>
                        @else
                            <span class="muted">Este pedido aún no registra un pago.</span>
                        @endif
                    </div>
                </td>
                <td width="4%"></td>
                <td width="44%">
                    <table class="totals">
                        <tr>
                            <td>Subtotal</td>
                            <td class="amount">{{ $record->sub_total->formatted }}</td>
                        </tr>
                        @if ($hasDiscount)
                            <tr>
                                <td>Descuento</td>
                                <td class="amount discount">-{{ $record->discount_total->formatted }}</td>
                            </tr>
                        @endif
                        @forelse ($record->shippingLines as $line)
                            <tr>
                                <td>Envío<br><span class="muted" style="font-size: 8px;">{{ strip_tags($line->description) }}</span></td>
                                <td class="amount">{{ $line->sub_total->value ? $line->sub_total->formatted : 'Gratis' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td>Envío</td>
                                <td class="amount">{{ $record->shipping_total->value ? $record->shipping_total->formatted : 'Gratis' }}</td>
                            </tr>
                        @endforelse
                        @if ($record->tax_total->value > 0)
                            <tr>
                                <td>IVA</td>
                                <td class="amount">{{ $record->tax_total->formatted }}</td>
                            </tr>
                        @endif
                        <tr class="grand">
                            <td>Total {{ $record->currency_code }}</td>
                            <td class="amount">{{ $record->total->formatted }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        @if ($record->customer_reference)
            <div class="notes"><strong>Referencia del cliente:</strong> {{ $record->customer_reference }}</div>
        @endif

        @if ($record->notes)
            <div class="notes"><strong>Notas del pedido</strong><br>{{ $record->notes }}</div>
        @endif
    </div>

    <div class="footer">
        <table>
            <tr>
                <td width="70%">
                    <strong>¡Gracias por tu compra!</strong><br>
                    Este documento es un comprobante de pedido y no sustituye a un comprobante fiscal (CFDI).
                    Si necesitas factura, escríbenos a {{ $contactEmail }} con tu número de pedido.
                </td>
                <td width="30%" style="text-align: right;">
                    <strong>IFIX</strong> · Productos para detallado<br>
                    {{ $site }}
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
