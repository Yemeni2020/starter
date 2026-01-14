<?php

namespace App\Domain\Payments\Actions;

use App\Domain\Orders\Actions\CancelOrderAction;
use App\Domain\Orders\Actions\MarkOrderPaidAction;
use App\Domain\Payments\PaymentGatewayResolver;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class HandleWebhookAction
{
    public function __construct(
        private PaymentGatewayResolver $resolver,
        private MarkOrderPaidAction $markOrderPaidAction,
        private CancelOrderAction $cancelOrderAction
    ) {
    }

    public function execute(string $provider, Request $request): ?Order
    {
        $gateway = $this->resolver->resolve($provider);

        if (!$gateway->verifyWebhook($request)) {
            throw new AccessDeniedHttpException('Invalid webhook signature.');
        }

        $data = $gateway->parseWebhook($request);

        return DB::transaction(function () use ($provider, $data) {
            $payment = Payment::query()
                ->where('provider', $provider)
                ->when($data->providerReference, function ($q) use ($data) {
                    $q->where('provider_reference', $data->providerReference);
                })
                ->latest()
                ->first();

            $order = null;
            if ($payment) {
                $order = $payment->order;
            }
            if (!$order && $data->orderNumber) {
                $order = Order::query()->where('order_number', $data->orderNumber)->first();
            }

            if ($payment) {
                $payment->status = $data->status;
                $payment->response_payload = $data->raw;
                if ($data->status === 'paid') {
                    $payment->paid_at = now();
                } elseif ($data->status === 'failed') {
                    $payment->failed_at = now();
                }
                $payment->save();
            }

            if (!$order) {
                return null;
            }

            $normalized = strtolower($data->status);

            if (in_array($normalized, ['paid', 'success', 'succeeded'], true)) {
                return $this->markOrderPaidAction->execute($order);
            }

            if (in_array($normalized, ['failed', 'canceled', 'cancelled'], true)) {
                return $this->cancelOrderAction->execute($order);
            }

            return $order;
        });
    }
}
