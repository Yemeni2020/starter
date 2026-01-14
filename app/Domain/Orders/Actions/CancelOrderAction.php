<?php

namespace App\Domain\Orders\Actions;

use App\Models\Order;
use App\Models\Product;
use App\Notifications\OrderStatusNotification;
use Illuminate\Support\Facades\DB;

class CancelOrderAction
{
    public function execute(Order $order): Order
    {
        return DB::transaction(function () use ($order) {
            $order->loadMissing('items');

            foreach ($order->items as $item) {
                $product = Product::query()->lockForUpdate()->find($item->product_id);
                if (!$product) {
                    continue;
                }

                $product->reserved_stock = max(0, $product->reserved_stock - $item->qty);
                $product->save();
            }

            $order->status = 'cancelled';
            if ($order->payment_status !== 'paid') {
                $order->payment_status = 'failed';
            }
            $order->save();
            $order->loadMissing('user');
            $order->user?->notify(new OrderStatusNotification($order, 'cancelled'));

            return $order;
        });
    }
}
