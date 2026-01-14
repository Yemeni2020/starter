<?php

namespace App\Domain\Payments\DTO;

class PaymentInitResult
{
    public function __construct(
        public readonly string $provider,
        public readonly string $reference,
        public readonly string $redirectUrl,
        public readonly array $payload = []
    ) {
    }
}
