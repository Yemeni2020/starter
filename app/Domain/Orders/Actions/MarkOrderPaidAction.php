<?php

namespace App\Domain\Orders\Actions;

use App\Models\Order;
use App\Models\Product;
use App\Notifications\OrderStatusNotification;
use Illuminate\Support\Facades\DB;

class MarkOrderPaidAction
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

                $product->stock = max(0, $product->stock - $item->qty);
                $product->reserved_stock = max(0, $product->reserved_stock - $item->qty);
                $product->save();
            }

            $order->status = 'processing';
            $order->payment_status = 'paid';
            $order->save();
            $order->loadMissing('user');

            $order->user?->notify(new OrderStatusNotification($order, 'processing'));

            return $order;
        });
    }
}
