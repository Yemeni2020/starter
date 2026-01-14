<?php

namespace App\Domain\Payments\Gateways;

use App\Domain\Payments\Contracts\PaymentGateway;
use App\Domain\Payments\DTO\PaymentInitResult;
use App\Domain\Payments\DTO\PaymentWebhookData;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MockGateway implements PaymentGateway
{
    public function __construct(private array $config = [])
    {
    }

    public function initiatePayment(Order $order): PaymentInitResult
    {
        $reference = 'MOCK-' . Str::upper(Str::random(10));
        $redirectUrl = $this->buildRedirectUrl($order, $reference);

        return new PaymentInitResult('mock', $reference, $redirectUrl, [
            'order_number' => $order->order_number,
            'amount' => $order->total,
            'currency' => config('store.currency', 'SAR'),
        ]);
    }

    public function verifyWebhook(Request $request): bool
    {
        return true;
    }

    public function parseWebhook(Request $request): PaymentWebhookData
    {
        return new PaymentWebhookData(
            'mock',
            $request->input('reference'),
            $request->input('order_number') ?? $request->input('order'),
            $request->input('status', 'paid'),
            $request->input('amount'),
            $request->input('currency') ?? config('store.currency', 'SAR'),
            $request->all()
        );
    }

    public function refundPayment(Order $order, ?float $amount = null): bool
    {
        return true;
    }

    private function buildRedirectUrl(Order $order, string $reference): string
    {
        $base = str_replace('{provider}', 'mock', config('payments.return_url'));
        $separator = str_contains($base, '?') ? '&' : '?';

        return $base . $separator . http_build_query([
            'order' => $order->order_number,
            'ref' => $reference,
            'status' => 'paid',
        ]);
    }
}
