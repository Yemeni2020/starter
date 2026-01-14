<?php

namespace App\Domain\Cart\Actions;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class GetCartAction
{
    public function execute(?User $user, ?string $sessionId, ?string $currency = null): Cart
    {
        $currency = $currency ?: config('store.currency', 'SAR');

        return DB::transaction(function () use ($user, $sessionId, $currency) {
            if ($user) {
                $cart = Cart::query()->firstOrCreate(
                    ['user_id' => $user->id],
                    ['currency' => $currency]
                );

                if ($sessionId) {
                    $guestCart = Cart::query()->where('session_id', $sessionId)->first();
                    if ($guestCart && $guestCart->id !== $cart->id) {
                        $guestCart->items->each(function (CartItem $item) use ($cart) {
                            $existing = $cart->items()->where('product_id', $item->product_id)->first();
                            if ($existing) {
                                $existing->qty += $item->qty;
                                $existing->save();
                            } else {
                                $item->cart_id = $cart->id;
                                $item->save();
                            }
                        });
                        $guestCart->delete();
                    }
                }

                return $cart->load('items.product');
            }

            $sessionId = $sessionId ?: session()->getId();
            $cart = Cart::query()->firstOrCreate(
                ['session_id' => $sessionId],
                ['currency' => $currency]
            );

            return $cart->load('items.product');
        });
    }
}
