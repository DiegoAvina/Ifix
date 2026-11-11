<?php

namespace App\Services\Order;

use App\Mail\Orders\OrderFreeShippingApplied;
use App\Models\OrderFreeShipping;
use App\Services\Payment\PaymentOrderService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use InvalidArgumentException;
use Lunar\Base\ValueObjects\Cart\ShippingBreakdown;
use Lunar\Base\ValueObjects\Cart\ShippingBreakdownItem;
use Lunar\DataTypes\Price;
use Lunar\Models\Order;

/**
 * "Envío gratis" desde el panel, solo en pedidos que no se han pagado:
 *
 * - apply():  deja el envío en $0, baja el total y avisa al cliente por correo.
 * - remove(): regresa el costo de envío original.
 * - settleToPaidAmount(): si el cliente paga con una página de pago abierta
 *   antes del cambio, el pedido se ajusta a lo que pagó (con o sin envío),
 *   para que nunca haya diferencia que reembolsar.
 *
 * Mercado Pago cobra el total que tiene el pedido al pulsar "Pagar".
 */
class FreeShippingService
{
    public function __construct(
        protected PaymentOrderService $payments,
    ) {}

    /**
     * Por qué no se puede aplicar, o null si sí se puede.
     */
    public function whyNotApply(Order $order): ?string
    {
        if ($reason = $this->whyNotEditable($order)) {
            return $reason;
        }

        if ($this->isApplied($order)) {
            return 'Este pedido ya tiene envío gratis.';
        }

        if ($order->shipping_total->value <= 0) {
            return 'Este pedido no cobra envío.';
        }

        return null;
    }

    public function canApply(Order $order): bool
    {
        return $this->whyNotApply($order) === null;
    }

    public function canRemove(Order $order): bool
    {
        return $this->whyNotEditable($order) === null && $this->isApplied($order);
    }

    public function isApplied(Order $order): bool
    {
        return (bool) ($this->state($order)['active'] ?? false);
    }

    /**
     * @return int Monto de envío retirado, en centavos.
     */
    public function apply(Order $order, ?Model $staff = null, bool $notifyCustomer = true): int
    {
        $removed = DB::transaction(function () use ($order, $staff, $notifyCustomer) {
            $order = $this->lock($order);

            if ($reason = $this->whyNotApply($order)) {
                throw new InvalidArgumentException($reason);
            }

            $removed = $this->removeShipping($order, $staff);

            $this->note($order, $staff, 'Envío gratis aplicado: se quitaron $'.$this->money($removed)
                .' MXN de envío. Nuevo total: $'.$this->money($order->total->value).' MXN.'
                .($notifyCustomer ? ' Se avisó al cliente por correo.' : ''));

            return $removed;
        });

        if ($notifyCustomer) {
            DB::afterCommit(fn () => $this->notifyCustomer($order->fresh()));
        }

        return $removed;
    }

    /**
     * Regresa el costo de envío que tenía el pedido.
     *
     * @return int Monto de envío restaurado, en centavos.
     */
    public function remove(Order $order, ?Model $staff = null): int
    {
        return DB::transaction(function () use ($order, $staff) {
            $order = $this->lock($order);

            if (! $this->canRemove($order)) {
                throw new InvalidArgumentException($this->whyNotEditable($order) ?? 'Este pedido no tiene envío gratis aplicado.');
            }

            $restored = $this->restoreShipping($order);

            $this->note($order, $staff, 'Envío gratis quitado: se regresó el envío de $'.$this->money($restored)
                .' MXN. Total: $'.$this->money($order->total->value).' MXN.');

            return $restored;
        });
    }

    /**
     * Pago aprobado de un pedido al que se le aplicó (o quitó) envío gratis:
     * si el monto pagado corresponde a la otra versión del pedido (con o sin
     * envío), el pedido se ajusta a ella. Devuelve true si el monto cuadra.
     *
     * Se llama dentro de la transacción del webhook, con el pedido bloqueado y
     * antes de registrar el pago.
     */
    public function settleToPaidAmount(Order $order, float $paidAmount): bool
    {
        $state = $this->state($order);

        if (! $state) {
            return false;
        }

        $paid = $this->toCents($order, $paidAmount);

        if (abs($paid - $order->total->value) <= 1) {
            return true;
        }

        if (abs($paid - (int) $state['total_with_shipping']) <= 1 && $this->isApplied($order)) {
            $this->restoreShipping($order);
            $this->note($order, null, 'El cliente pagó con una página de pago abierta antes del envío gratis ($'
                .$this->money($paid).' MXN). Se regresó el envío al pedido para que coincida con lo pagado; no hay nada que reembolsar.');

            return true;
        }

        if (abs($paid - (int) $state['total_without_shipping']) <= 1 && ! $this->isApplied($order)) {
            $this->removeShipping($order);
            $this->note($order, null, 'El cliente pagó sin envío ($'.$this->money($paid)
                .' MXN) con una página de pago abierta mientras tenía envío gratis. Se volvió a aplicar el envío gratis para que coincida con lo pagado.');

            return true;
        }

        return false;
    }

    /**
     * El monto pagado cuadra con el pedido con envío o sin envío.
     */
    public function matchesAlternativeTotal(Order $order, float $paidAmount): bool
    {
        $state = $this->state($order);

        if (! $state) {
            return false;
        }

        $paid = $this->toCents($order, $paidAmount);

        return abs($paid - (int) $state['total_with_shipping']) <= 1
            || abs($paid - (int) $state['total_without_shipping']) <= 1;
    }

    private function whyNotEditable(Order $order): ?string
    {
        if ($order->placed_at || $this->payments->hasSuccessfulCapture($order)) {
            return 'El pedido ya está pagado; el envío ya no se puede cambiar.';
        }

        if ($order->status !== 'awaiting-payment') {
            return 'Solo se puede cambiar en pedidos en "Esperando pago".';
        }

        return null;
    }

    /**
     * Deja el envío en $0 y guarda lo necesario para regresarlo. No valida:
     * quien llama ya decidió que procede.
     */
    private function removeShipping(Order $order, ?Model $staff = null): int
    {
        $previous = $this->state($order);

        // Si ya se había aplicado y quitado antes, se conservan los montos originales.
        $shippingLines = $previous['lines'] ?? $order->shippingLines->map(fn ($line) => [
            'id' => $line->id,
            'unit_price' => $line->unit_price->value,
            'sub_total' => $line->sub_total->value,
            'tax_total' => $line->tax_total->value,
            'total' => $line->total->value,
        ])->all();

        $removedTotal = (int) collect($shippingLines)->sum('total');
        $removedTax = (int) collect($shippingLines)->sum('tax_total');
        $totalWithShipping = $order->total->value;

        $this->setShippingLines($order, fn () => ['unit_price' => 0, 'sub_total' => 0, 'tax_total' => 0, 'total' => 0]);

        $this->updateOrder($order, [
            'shipping_total' => 0,
            'tax_total' => max(0, $order->tax_total->value - $removedTax),
            'total' => max(0, $totalWithShipping - $removedTotal),
        ], shippingValue: 0, state: [
            'active' => true,
            'shipping_total' => (int) collect($shippingLines)->sum('sub_total'),
            'shipping_tax' => $removedTax,
            'lines' => $shippingLines,
            'total_with_shipping' => $totalWithShipping,
            'total_without_shipping' => max(0, $totalWithShipping - $removedTotal),
            'applied_at' => now()->toIso8601String(),
            'applied_by' => $staff?->getKey(),
        ]);

        return $removedTotal;
    }

    private function restoreShipping(Order $order): int
    {
        $state = $this->state($order);
        $lines = collect($state['lines'] ?? [])->keyBy('id');

        $this->setShippingLines($order, fn ($line) => collect($lines->get($line->id, []))
            ->only(['unit_price', 'sub_total', 'tax_total', 'total'])->all());

        $restored = (int) ($state['shipping_total'] ?? 0);

        $this->updateOrder($order, [
            'shipping_total' => $restored,
            'tax_total' => $order->tax_total->value + (int) ($state['shipping_tax'] ?? 0),
            'total' => $order->total->value + (int) $lines->sum('total'),
        ], shippingValue: null, state: ['active' => false] + $state);

        return $restored;
    }

    private function setShippingLines(Order $order, callable $values): void
    {
        foreach ($order->shippingLines as $line) {
            $line->update($values($line));
        }
    }

    /**
     * @param  int|null  $shippingValue  Valor de cada opción del desglose de envío (null = el original de cada línea).
     */
    private function updateOrder(Order $order, array $attributes, ?int $shippingValue, array $state): void
    {
        $breakdown = $order->shipping_breakdown;
        $originalLines = collect($state['lines'] ?? [])->values();

        if ($breakdown instanceof ShippingBreakdown) {
            $breakdown = new ShippingBreakdown($breakdown->items->values()->map(
                fn (ShippingBreakdownItem $item, int $i) => new ShippingBreakdownItem(
                    $item->name,
                    $item->identifier,
                    new Price($shippingValue ?? (int) ($originalLines[$i]['sub_total'] ?? 0), $order->currency, 1),
                )
            ));
        }

        $order->update($attributes + ['shipping_breakdown' => $breakdown]);

        // Aparte de `meta`: el panel muestra el meta del pedido tal cual.
        OrderFreeShipping::query()->updateOrCreate(
            ['order_id' => $order->id],
            ['active' => (bool) $state['active'], 'state' => $state],
        );
    }

    private function notifyCustomer(Order $order): void
    {
        $recipient = $order->billingAddress?->contact_email
            ?? $order->shippingAddress?->contact_email
            ?? $order->user?->email;

        if (! $recipient) {
            Log::warning('order.free_shipping_email.no_recipient', ['order_id' => $order->id]);

            return;
        }

        Mail::to($recipient)->queue(new OrderFreeShippingApplied($order));
    }

    private function note(Order $order, ?Model $staff, string $content): void
    {
        activity()
            ->useLog('lunarpanel')
            ->performedOn($order)
            ->causedBy($staff)
            ->event('comment')
            ->withProperties(['content' => $content])
            ->log('comment');
    }

    private function lock(Order $order): Order
    {
        return Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
    }

    private function state(Order $order): ?array
    {
        $record = OrderFreeShipping::query()->where('order_id', $order->id)->first();

        if (! $record || ! isset($record->state['total_with_shipping'])) {
            return null;
        }

        return ['active' => $record->active] + $record->state;
    }

    private function toCents(Order $order, float $amount): int
    {
        return (int) round($amount * (10 ** ($order->currency?->decimal_places ?? 2)));
    }

    private function money(int $cents): string
    {
        return number_format($cents / 100, 2);
    }
}
