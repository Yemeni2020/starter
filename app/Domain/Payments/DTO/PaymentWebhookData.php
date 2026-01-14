<?php

namespace App\Domain\Payments\DTO;

class PaymentWebhookData
{
    public function __construct(
        public readonly string $provider,
        public readonly ?string $providerReference,
        public readonly ?string $orderNumber,
        public readonly string $status,
        public readonly ?float $amount,
        public readonly ?string $currency,
        public readonly array $raw = []
    ) {
    }
}
