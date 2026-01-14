<?php

namespace App\Services\Reports;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ReportService
{
    public function salesReport(?Carbon $start, ?Carbon $end): array
    {
        $orders = $this->ordersQuery($start, $end);
        $totalOrders = (clone $orders)->count();
        $revenue = (clone $orders)->sum('total');
        $paidOrders = (clone $orders)->where('payment_status', 'paid')->count();
        $unpaidOrders = $totalOrders - $paidOrders;

        $topProducts = $this->topProducts($start, $end);

        return [
            'total_orders' => $totalOrders,
            'revenue' => $revenue,
            'paid_orders' => $paidOrders,
            'unpaid_orders' => $unpaidOrders,
            'top_products' => $topProducts,
        ];
    }

    public function productsReport(?Carbon $start, ?Carbon $end): array
    {
        $lowStock = Product::query()
            ->orderBy('stock')
            ->where('stock', '<=', 5)
            ->take(10)
            ->get();

        $bestSellers = $this->topProducts($start, $end);

        return [
            'low_stock' => $lowStock,
            'best_sellers' => $bestSellers,
        ];
    }

    public function customersReport(?Carbon $start, ?Carbon $end): array
    {
        $newCustomers = User::query()
            ->when($start && $end, fn ($query) => $query->whereBetween('created_at', [$start, $end]))
            ->count();

        $topSpenders = Order::query()
            ->selectRaw('user_id, SUM(total) as total_spent, COUNT(*) as orders_count')
            ->whereNotNull('user_id')
            ->when($start && $end, fn ($query) => $query->whereBetween('created_at', [$start, $end]))
            ->groupBy('user_id')
            ->orderByDesc('total_spent')
            ->with('user')
            ->take(10)
            ->get();

        return [
            'new_customers' => $newCustomers,
            'top_spenders' => $topSpenders,
        ];
    }

    private function ordersQuery(?Carbon $start, ?Carbon $end)
    {
        return Order::query()
            ->when($start && $end, fn ($query) => $query->whereBetween('created_at', [$start, $end]));
    }

    private function topProducts(?Carbon $start, ?Carbon $end): Collection
    {
        return OrderItem::query()
            ->selectRaw('product_id, SUM(qty) as total_qty, SUM(total) as total_revenue')
            ->when($start && $end, function ($query) use ($start, $end) {
                $query->whereHas('order', fn ($order) => $order->whereBetween('created_at', [$start, $end]));
            })
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->with('product')
            ->take(10)
            ->get();
    }
}
