<?php

namespace App\Domain\Payments\Contracts;

use App\Domain\Payments\DTO\PaymentInitResult;
use App\Domain\Payments\DTO\PaymentWebhookData;
use App\Models\Order;
use Illuminate\Http\Request;

interface PaymentGateway
{
    public function initiatePayment(Order $order): PaymentInitResult;

    public function verifyWebhook(Request $request): bool;

    public function parseWebhook(Request $request): PaymentWebhookData;

    public function refundPayment(Order $order, ?float $amount = null): bool;
}
