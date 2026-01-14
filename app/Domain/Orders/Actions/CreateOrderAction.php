<?php

namespace App\Domain\Orders\Actions;

use App\Notifications\OrderStatusNotification;
use App\Domain\Orders\Actions\QuoteTotalsAction;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CreateOrderAction
{
    public function __construct(private QuoteTotalsAction $quoteTotalsAction)
    {
    }

    public function execute(Cart $cart, Address $shippingAddress, ?Address $billingAddress, array $data = []): Order
    {
        $cart->loadMissing('items.product', 'user');

        if ($cart->items->isEmpty()) {
            throw ValidationException::withMessages(['cart' => 'Cart is empty.']);
        }

        return DB::transaction(function () use ($cart, $shippingAddress, $billingAddress, $data) {
            $totals = $this->quoteTotalsAction->execute($cart, $data['discount_total'] ?? null);
            $orderNumber = $this->generateOrderNumber();

            $order = Order::create([
                'user_id' => $cart->user_id,
                'order_number' => $orderNumber,
                'status' => 'awaiting_payment',
                'subtotal' => $totals['subtotal'],
                'shipping_fee' => $totals['shipping_fee'],
                'discount_total' => $totals['discount_total'],
                'tax_total' => $totals['tax_total'],
                'total' => $totals['total'],
                'payment_status' => 'unpaid',
                'payment_method' => $data['payment_method'] ?? null,
                'shipping_address_id' => $shippingAddress->id,
                'billing_address_id' => $billingAddress?->id,
                'notes' => $data['notes'] ?? null,
                'placed_at' => now(),
            ]);

            foreach ($cart->items as $item) {
                $product = Product::query()->lockForUpdate()->findOrFail($item->product_id);
                if ($product->availableStock() < $item->qty) {
                    throw ValidationException::withMessages([
                        'stock' => "Insufficient stock for {$product->name}.",
                    ]);
                }

                $product->reserved_stock += $item->qty;
                $product->save();

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'name_snapshot' => $product->name,
                    'sku_snapshot' => $product->sku,
                    'qty' => $item->qty,
                    'price' => $item->price_snapshot,
                    'total' => $item->price_snapshot * $item->qty,
                ]);
            }

            $cart->items()->delete();

            $order->load(['items.product', 'shippingAddress', 'billingAddress', 'user']);

            $cart->user?->notify(new OrderStatusNotification($order, 'created'));

            return $order;
        });
    }

    private function generateOrderNumber(): string
    {
        return 'ORD-' . strtoupper(Str::random(8));
    }
}
