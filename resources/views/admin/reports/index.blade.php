@extends('admin.layouts.app')

@section('content')
    @php
        $exportParams = array_filter([
            'start' => $start?->toDateString(),
            'end' => $end?->toDateString(),
        ]);
    @endphp

    <div class="flex w-full flex-1 flex-col gap-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="space-y-1">
                <flux:heading size="xl" level="1">{{ __('Reports') }}</flux:heading>
                <flux:text>{{ __('Monitor performance and export reports.') }}</flux:text>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.reports.index') }}" class="flex flex-col gap-4 rounded-xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-700 dark:bg-zinc-900 lg:flex-row lg:items-end">
            <flux:select name="type" label="{{ __('Report type') }}">
                <flux:select.option value="sales" :selected="$type === 'sales'">{{ __('Sales') }}</flux:select.option>
                <flux:select.option value="products" :selected="$type === 'products'">{{ __('Products') }}</flux:select.option>
                <flux:select.option value="customers" :selected="$type === 'customers'">{{ __('Customers') }}</flux:select.option>
            </flux:select>
            <flux:input name="start" type="date" label="{{ __('Start date') }}" value="{{ $start?->toDateString() }}" />
            <flux:input name="end" type="date" label="{{ __('End date') }}" value="{{ $end?->toDateString() }}" />
            <flux:button type="submit" variant="primary">{{ __('Apply') }}</flux:button>
        </form>

        <div class="flex flex-wrap gap-3">
            <flux:button variant="outline" :href="route('admin.reports.export', array_merge(['type' => $type, 'format' => 'excel'], $exportParams))">
                {{ __('Export Excel') }}
            </flux:button>
            <flux:button variant="outline" :href="route('admin.reports.export', array_merge(['type' => $type, 'format' => 'pdf'], $exportParams))">
                {{ __('Export PDF') }}
            </flux:button>
            <flux:button variant="outline" :href="route('admin.reports.export', array_merge(['type' => $type, 'format' => 'print'], $exportParams))" target="_blank">
                {{ __('Print view') }}
            </flux:button>
        </div>

        @if ($type === 'sales')
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
                    <flux:text variant="subtle">{{ __('Orders') }}</flux:text>
                    <flux:heading size="xl" level="2">{{ $reportData['total_orders'] ?? 0 }}</flux:heading>
                </div>
                <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
                    <flux:text variant="subtle">{{ __('Revenue') }}</flux:text>
                    <flux:heading size="xl" level="2">{{ number_format((float) ($reportData['revenue'] ?? 0), 2) }}</flux:heading>
                </div>
                <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
                    <flux:text variant="subtle">{{ __('Paid orders') }}</flux:text>
                    <flux:heading size="xl" level="2">{{ $reportData['paid_orders'] ?? 0 }}</flux:heading>
                </div>
                <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
                    <flux:text variant="subtle">{{ __('Unpaid orders') }}</flux:text>
                    <flux:heading size="xl" level="2">{{ $reportData['unpaid_orders'] ?? 0 }}</flux:heading>
                </div>
            </div>

            <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="lg" level="2">{{ __('Top products') }}</flux:heading>
                <div class="mt-4 divide-y divide-zinc-100 text-sm dark:divide-zinc-800">
                    @forelse ($reportData['top_products'] ?? [] as $item)
                        <div class="flex items-center justify-between py-3">
                            <span class="font-medium text-zinc-900 dark:text-white">{{ $item->product?->name ?? __('Unknown') }}</span>
                            <span class="text-zinc-500">{{ $item->total_qty ?? 0 }} • {{ number_format((float) ($item->total_revenue ?? 0), 2) }}</span>
                        </div>
                    @empty
                        <div class="py-3 text-zinc-500">{{ __('No sales data available.') }}</div>
                    @endforelse
                </div>
            </div>
        @elseif ($type === 'products')
            <div class="grid gap-6 lg:grid-cols-2">
                <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
                    <flux:heading size="lg" level="2">{{ __('Low stock') }}</flux:heading>
                    <div class="mt-4 divide-y divide-zinc-100 text-sm dark:divide-zinc-800">
                        @forelse ($reportData['low_stock'] ?? [] as $product)
                            <div class="flex items-center justify-between py-3">
                                <span class="font-medium text-zinc-900 dark:text-white">{{ $product->name }}</span>
                                <span class="text-zinc-500">{{ $product->stock }}</span>
                            </div>
                        @empty
                            <div class="py-3 text-zinc-500">{{ __('No low stock items.') }}</div>
                        @endforelse
                    </div>
                </div>
                <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
                    <flux:heading size="lg" level="2">{{ __('Best sellers') }}</flux:heading>
                    <div class="mt-4 divide-y divide-zinc-100 text-sm dark:divide-zinc-800">
                        @forelse ($reportData['best_sellers'] ?? [] as $item)
                            <div class="flex items-center justify-between py-3">
                                <span class="font-medium text-zinc-900 dark:text-white">{{ $item->product?->name ?? __('Unknown') }}</span>
                                <span class="text-zinc-500">{{ $item->total_qty ?? 0 }} • {{ number_format((float) ($item->total_revenue ?? 0), 2) }}</span>
                            </div>
                        @empty
                            <div class="py-3 text-zinc-500">{{ __('No sales data available.') }}</div>
                        @endforelse
                    </div>
                </div>
            </div>
        @else
            <div class="grid gap-6 lg:grid-cols-2">
                <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
                    <flux:heading size="lg" level="2">{{ __('New customers') }}</flux:heading>
                    <div class="mt-4 text-3xl font-semibold text-zinc-900 dark:text-white">
                        {{ $reportData['new_customers'] ?? 0 }}
                    </div>
                </div>
                <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
                    <flux:heading size="lg" level="2">{{ __('Top spenders') }}</flux:heading>
                    <div class="mt-4 divide-y divide-zinc-100 text-sm dark:divide-zinc-800">
                        @forelse ($reportData['top_spenders'] ?? [] as $item)
                            <div class="flex items-center justify-between py-3">
                                <span class="font-medium text-zinc-900 dark:text-white">{{ $item->user?->name ?? __('Unknown') }}</span>
                                <span class="text-zinc-500">{{ number_format((float) ($item->total_spent ?? 0), 2) }} • {{ $item->orders_count ?? 0 }}</span>
                            </div>
                        @empty
                            <div class="py-3 text-zinc-500">{{ __('No customer data available.') }}</div>
                        @endforelse
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
