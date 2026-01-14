<?php

namespace App\Domain\Payments\Actions;

use App\Domain\Payments\DTO\PaymentInitResult;
use App\Domain\Payments\PaymentGatewayResolver;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class InitPaymentAction
{
    public function __construct(private PaymentGatewayResolver $resolver)
    {
    }

    public function execute(Order $order, ?string $provider = null): PaymentInitResult
    {
        return DB::transaction(function () use ($order, $provider) {
            $gateway = $this->resolver->resolve($provider);
            $result = $gateway->initiatePayment($order);

            Payment::create([
                'order_id' => $order->id,
                'provider' => $result->provider,
                'provider_reference' => $result->reference,
                'status' => 'initiated',
                'amount' => $order->total,
                'currency' => config('store.currency', 'SAR'),
                'request_payload' => $result->payload,
            ]);

            $order->payment_method = $result->provider;
            $order->status = 'awaiting_payment';
            $order->payment_status = 'unpaid';
            $order->save();

            return $result;
        });
    }
}
