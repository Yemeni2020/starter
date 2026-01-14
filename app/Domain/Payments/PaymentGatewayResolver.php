<?php

namespace App\Domain\Payments;

use App\Domain\Payments\Contracts\PaymentGateway;
use App\Domain\Payments\Gateways\MadaGateway;
use App\Domain\Payments\Gateways\MockGateway;
use App\Domain\Payments\Gateways\StcPayGateway;
use InvalidArgumentException;

class PaymentGatewayResolver
{
    public function resolve(?string $provider = null): PaymentGateway
    {
        $provider = $provider ?: config('payments.default', 'mock');
        $provider = strtolower($provider);

        return match ($provider) {
            'mada' => new MadaGateway(config('payments.providers.mada', [])),
            'stcpay' => new StcPayGateway(config('payments.providers.stcpay', [])),
            'mock' => new MockGateway(config('payments.providers.mock', [])),
            default => throw new InvalidArgumentException("Unsupported payment provider: {$provider}"),
        };
    }
}
