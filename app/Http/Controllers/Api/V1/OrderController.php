<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Cart\Actions\GetCartAction;
use App\Domain\Orders\Actions\CreateOrderAction;
use App\Domain\Orders\Actions\QuoteTotalsAction;
use App\Http\Requests\CheckoutQuoteRequest;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Address;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends ApiController
{
    public function quote(CheckoutQuoteRequest $request, GetCartAction $getCartAction, QuoteTotalsAction $quoteTotalsAction)
    {
        $cart = $getCartAction->execute($request->user(), $this->sessionId($request));
        $totals = $quoteTotalsAction->execute($cart);

        return $this->success([
            'totals' => $totals,
        ]);
    }

    public function store(CreateOrderRequest $request, GetCartAction $getCartAction, CreateOrderAction $createOrderAction)
    {
        $user = $request->user();
        $cart = $getCartAction->execute($user, $this->sessionId($request));

        $shipping = Address::query()
            ->where('user_id', $user->id)
            ->findOrFail($request->input('shipping_address_id'));

        $billing = null;
        if ($request->filled('billing_address_id')) {
            $billing = Address::query()
                ->where('user_id', $user->id)
                ->findOrFail($request->input('billing_address_id'));
        }

        $order = $createOrderAction->execute($cart, $shipping, $billing, [
            'payment_method' => $request->input('payment_method'),
            'notes' => $request->input('notes'),
        ]);

        return $this->success((new OrderResource($order->load('items')))->resolve(), 'Order created.', 201);
    }

    public function index(Request $request)
    {
        $orders = $request->user()->orders()->latest()->with('items')->paginate(10);
        $payload = OrderResource::collection($orders)->response()->getData(true);

        return $this->success($payload);
    }

    public function show(Request $request, int $id)
    {
        $order = Order::query()->where('user_id', $request->user()->id)->with('items')->findOrFail($id);

        return $this->success((new OrderResource($order))->resolve());
    }

    private function sessionId(Request $request): ?string
    {
        return $request->header('X-Session-Id') ?: $request->session()->getId();
    }
}
