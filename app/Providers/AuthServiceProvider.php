<?php

namespace App\Providers;

use App\Models\Address;
use App\Models\Cart;
use App\Models\Order;
use App\Policies\AddressPolicy;
use App\Policies\CartPolicy;
use App\Policies\OrderPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Address::class => AddressPolicy::class,
        Cart::class => CartPolicy::class,
        Order::class => OrderPolicy::class,
    ];
}
