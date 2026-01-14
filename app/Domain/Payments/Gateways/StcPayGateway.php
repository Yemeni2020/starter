<?php

namespace App\Domain\Payments\Gateways;

use App\Domain\Payments\Contracts\PaymentGateway;
use App\Domain\Payments\DTO\PaymentInitResult;
use App\Domain\Payments\DTO\PaymentWebhookData;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StcPayGateway implements PaymentGateway
{
    public function __construct(private array $config = [])
    {
    }

    public function initiatePayment(Order $order): PaymentInitResult
    {
        $reference = 'STCPAY-' . Str::upper(Str::random(10));
        $redirectUrl = $this->buildRedirectUrl($order, $reference);

        return new PaymentInitResult('stcpay', $reference, $redirectUrl, [
            'merchant_id' => $this->config['merchant_id'] ?? null,
            'order_number' => $order->order_number,
            'amount' => $order->total,
            'currency' => config('store.currency', 'SAR'),
        ]);
    }

    public function verifyWebhook(Request $request): bool
    {
        $secret = config('payments.webhook_secret');
        if (!$secret) {
            return true;
        }

        return hash_equals($secret, (string) $request->header('X-Webhook-Secret'));
    }

    public function parseWebhook(Request $request): PaymentWebhookData
    {
        return new PaymentWebhookData(
            'stcpay',
            $request->input('reference'),
            $request->input('order_number') ?? $request->input('order'),
            $request->input('status', 'failed'),
            $request->input('amount'),
            $request->input('currency'),
            $request->all()
        );
    }

    public function refundPayment(Order $order, ?float $amount = null): bool
    {
        return false;
    }

    private function buildRedirectUrl(Order $order, string $reference): string
    {
        $base = str_replace('{provider}', 'stcpay', config('payments.return_url'));
        $separator = str_contains($base, '?') ? '&' : '?';

        return $base . $separator . http_build_query([
            'order' => $order->order_number,
            'ref' => $reference,
        ]);
    }
}
