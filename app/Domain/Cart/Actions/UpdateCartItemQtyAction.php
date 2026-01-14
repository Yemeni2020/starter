<?php

namespace App\Domain\Cart\Actions;

use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateCartItemQtyAction
{
    public function execute(CartItem $item, int $qty): CartItem
    {
        if ($qty < 1) {
            throw ValidationException::withMessages(['qty' => 'Quantity must be at least 1.']);
        }

        return DB::transaction(function () use ($item, $qty) {
            $lockedItem = CartItem::query()->lockForUpdate()->findOrFail($item->id);
            $product = Product::query()->lockForUpdate()->findOrFail($lockedItem->product_id);

            if ($product->availableStock() < $qty) {
                throw ValidationException::withMessages([
                    'qty' => 'Insufficient stock for requested quantity.',
                ]);
            }

            $lockedItem->qty = $qty;
            $lockedItem->price_snapshot = $product->price;
            $lockedItem->save();

            return $lockedItem;
        });
    }
}
