<?php

namespace App\Data\Payment;

/**
 * Datos necesarios para crear una Preference de Checkout Pro a partir
 * de una Order ya validada. Todo proviene del servidor (Order/Lunar) —
 * nunca de datos enviados por el navegador.
 */
final readonly class PaymentData
{
    public function __construct(
        public int $orderId,
        public string $externalReference,
        public string $description,
        public float $amount,
        public string $currency,
        public ?string $payerEmail,
    ) {}
}
