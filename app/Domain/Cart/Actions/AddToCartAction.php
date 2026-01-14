<?php

namespace App\Domain\Cart\Actions;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AddToCartAction
{
    public function execute(Cart $cart, Product $product, int $qty): CartItem
    {
        if ($qty < 1) {
            throw ValidationException::withMessages(['qty' => 'Quantity must be at least 1.']);
        }

        return DB::transaction(function () use ($cart, $product, $qty) {
            $lockedProduct = Product::query()->lockForUpdate()->findOrFail($product->id);

            $item = CartItem::query()
                ->where('cart_id', $cart->id)
                ->where('product_id', $product->id)
                ->lockForUpdate()
                ->first();

            $newQty = $qty + ($item?->qty ?? 0);

            if ($lockedProduct->availableStock() < $newQty) {
                throw ValidationException::withMessages([
                    'qty' => 'Insufficient stock for requested quantity.',
                ]);
            }

            if (!$item) {
                $item = new CartItem([
                    'cart_id' => $cart->id,
                    'product_id' => $product->id,
                ]);
            }

            $item->qty = $newQty;
            $item->price_snapshot = $lockedProduct->price;
            $item->save();

            return $item;
        });
    }
}
