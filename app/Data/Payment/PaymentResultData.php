<?php

namespace App\Data\Payment;

/**
 * Resultado de consultar el estado REAL de un pago directamente en la
 * API del proveedor (nunca se construye a partir del payload de un
 * webhook sin verificar, ni de datos enviados por el navegador).
 */
final readonly class PaymentResultData
{
    public function __construct(
        public string $providerPaymentId,
        public string $status,
        public ?string $statusDetail,
        public float $amount,
        public string $currency,
        public ?string $externalReference,
    ) {}

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Estados que representan un pago que aún puede resolverse
     * (no es definitivo todavía).
     */
    public function isPending(): bool
    {
        return in_array($this->status, ['pending', 'in_process', 'in_mediation'], true);
    }

    /**
     * Estados definitivos de "no vamos a cobrar esto".
     */
    public function isRejected(): bool
    {
        return in_array($this->status, ['rejected', 'cancelled'], true);
    }
}
