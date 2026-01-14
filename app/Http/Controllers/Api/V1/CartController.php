<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Cart\Actions\AddToCartAction;
use App\Domain\Cart\Actions\GetCartAction;
use App\Domain\Cart\Actions\RemoveCartItemAction;
use App\Domain\Cart\Actions\UpdateCartItemQtyAction;
use App\Domain\Orders\Actions\QuoteTotalsAction;
use App\Http\Requests\AddToCartRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Http\Resources\CartResource;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends ApiController
{
    public function index(Request $request, GetCartAction $action, QuoteTotalsAction $quoteTotalsAction)
    {
        $cart = $action->execute($request->user(), $this->sessionId($request));
        $totals = $quoteTotalsAction->execute($cart);

        return $this->success([
            'cart' => (new CartResource($cart))->resolve(),
            'totals' => $totals,
        ]);
    }

    public function store(AddToCartRequest $request, GetCartAction $getCartAction, AddToCartAction $addToCartAction, QuoteTotalsAction $quoteTotalsAction)
    {
        $cart = $getCartAction->execute($request->user(), $this->sessionId($request));
        $product = Product::query()->findOrFail($request->input('product_id'));
        $addToCartAction->execute($cart, $product, (int) $request->input('qty'));
        $cart->load('items.product');

        return $this->success([
            'cart' => (new CartResource($cart))->resolve(),
            'totals' => $quoteTotalsAction->execute($cart),
        ], 'Item added to cart.');
    }

    public function update(UpdateCartItemRequest $request, int $id, GetCartAction $getCartAction, UpdateCartItemQtyAction $updateCartItemQtyAction, QuoteTotalsAction $quoteTotalsAction)
    {
        $cart = $getCartAction->execute($request->user(), $this->sessionId($request));
        $item = CartItem::query()->where('cart_id', $cart->id)->findOrFail($id);

        $updateCartItemQtyAction->execute($item, (int) $request->input('qty'));
        $cart->load('items.product');

        return $this->success([
            'cart' => (new CartResource($cart))->resolve(),
            'totals' => $quoteTotalsAction->execute($cart),
        ], 'Cart item updated.');
    }

    public function destroy(Request $request, int $id, GetCartAction $getCartAction, RemoveCartItemAction $removeCartItemAction, QuoteTotalsAction $quoteTotalsAction)
    {
        $cart = $getCartAction->execute($request->user(), $this->sessionId($request));
        $item = CartItem::query()->where('cart_id', $cart->id)->findOrFail($id);

        $removeCartItemAction->execute($item);
        $cart->load('items.product');

        return $this->success([
            'cart' => (new CartResource($cart))->resolve(),
            'totals' => $quoteTotalsAction->execute($cart),
        ], 'Cart item removed.');
    }

    private function sessionId(Request $request): ?string
    {
        return $request->header('X-Session-Id') ?: $request->session()->getId();
    }
}
