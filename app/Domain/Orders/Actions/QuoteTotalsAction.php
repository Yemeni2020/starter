<?php

namespace App\Domain\Orders\Actions;

use App\Models\Cart;

class QuoteTotalsAction
{
    public function execute(Cart $cart, ?float $discountTotal = null): array
    {
        $cart->loadMissing('items.product');

        $subtotal = $cart->items->sum(function ($item) {
            return $item->price_snapshot * $item->qty;
        });

        $shippingFee = (float) config('store.shipping_fee', 0);
        $taxRate = (float) config('store.tax_rate', 0);
        $taxTotal = $taxRate > 0 ? round($subtotal * ($taxRate / 100), 2) : 0;
        $discountTotal = $discountTotal ?? 0;

        $total = max(0, $subtotal + $shippingFee + $taxTotal - $discountTotal);

        return [
            'subtotal' => $subtotal,
            'shipping_fee' => $shippingFee,
            'discount_total' => $discountTotal,
            'tax_total' => $taxTotal,
            'total' => $total,
        ];
    }
}
