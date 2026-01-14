<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Payments\Actions\InitPaymentAction;
use App\Jobs\ProcessPaymentWebhook;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PaymentController extends ApiController
{
    public function init(Request $request, Order $order, InitPaymentAction $action)
    {
        $this->authorize('view', $order);

        $provider = $request->input('provider') ?? $order->payment_method;
        $result = $action->execute($order, $provider);

        return $this->success([
            'provider' => $result->provider,
            'reference' => $result->reference,
            'redirect_url' => $result->redirectUrl,
            'payload' => $result->payload,
        ], 'Payment initiated.');
    }

    public function status(Request $request, Order $order)
    {
        $this->authorize('view', $order);

        $payment = $order->payments()->latest()->first();

        return $this->success([
            'order_number' => $order->order_number,
            'payment_status' => $order->payment_status,
            'provider' => $payment?->provider,
            'provider_reference' => $payment?->provider_reference,
            'status' => $payment?->status,
        ]);
    }

    public function madaWebhook(Request $request)
    {
        ProcessPaymentWebhook::dispatch('mada', $request->all(), $this->headersForQueue($request));

        return $this->success(null, 'Webhook queued.');
    }

    public function stcPayWebhook(Request $request)
    {
        ProcessPaymentWebhook::dispatch('stcpay', $request->all(), $this->headersForQueue($request));

        return $this->success(null, 'Webhook queued.');
    }

    private function headersForQueue(Request $request): array
    {
        return Collection::make($request->headers->all())
            ->map(fn ($values) => $values[0] ?? null)
            ->filter()
            ->all();
    }
}
