<?php

namespace App\Domain\Payments;

use App\Domain\Payments\Contracts\PaymentGateway;
use App\Domain\Payments\Gateways\MadaGateway;
use App\Domain\Payments\Gateways\MockGateway;
use App\Domain\Payments\Gateways\StcPayGateway;
use App\Models\PaymentGatewaySetting;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;

class PaymentGatewayResolver
{
    public function resolve(?string $provider = null): PaymentGateway
    {
        $provider = $provider ?: $this->resolveDefaultProvider();
        $provider = strtolower($provider);

        $config = $this->resolveProviderConfig($provider);

        if (array_key_exists('enabled', $config) && ! $config['enabled']) {
            throw new InvalidArgumentException("Payment provider disabled: {$provider}");
        }

        return match ($provider) {
            'mada' => new MadaGateway($config),
            'stcpay' => new StcPayGateway($config),
            'mock' => new MockGateway($config),
            default => throw new InvalidArgumentException("Unsupported payment provider: {$provider}"),
        };
    }

    private function resolveDefaultProvider(): string
    {
        if (Schema::hasTable('payment_gateway_settings')) {
            $setting = PaymentGatewaySetting::query()
                ->where('enabled', true)
                ->orderBy('sort_order')
                ->first();

            if ($setting) {
                return $setting->provider;
            }
        }

        return config('payments.default', 'mock');
    }

    private function resolveProviderConfig(string $provider): array
    {
        $config = config("payments.providers.{$provider}", []);

        if (! Schema::hasTable('payment_gateway_settings')) {
            return $config;
        }

        $setting = PaymentGatewaySetting::where('provider', $provider)->first();
        if (! $setting) {
            return $config;
        }

        $credentials = $setting->credentials ?? [];

        return array_merge($config, $credentials, [
            'enabled' => $setting->enabled,
            'sandbox' => $setting->sandbox,
            'webhook_secret' => $setting->webhook_secret,
            'display_name' => $setting->display_name,
        ]);
    }
}
