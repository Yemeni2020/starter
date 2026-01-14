@extends('admin.layouts.app')

@section('content')
    <div class="flex w-full flex-1 flex-col gap-6">
        <div class="flex flex-col gap-3">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="space-y-1">
                    <flux:heading size="xl" level="1">Orders</flux:heading>
                    <flux:text>Track fulfillment, payments, and shipment status.</flux:text>
                </div>
                <div class="flex flex-wrap gap-3">
                    <flux:button variant="outline" icon="arrow-down-tray" icon:variant="outline">Export</flux:button>
                    <flux:button variant="primary" icon="plus" icon:variant="outline">Create order</flux:button>
                </div>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
                    <flux:text variant="subtle">Today</flux:text>
                    <flux:heading size="xl" level="2">38</flux:heading>
                    <flux:text size="sm" class="mt-2">Orders placed</flux:text>
                </div>
                <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
                    <flux:text variant="subtle">Pending</flux:text>
                    <flux:heading size="xl" level="2">12</flux:heading>
                    <flux:text size="sm" class="mt-2">Awaiting payment</flux:text>
                </div>
                <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
                    <flux:text variant="subtle">Fulfillment</flux:text>
                    <flux:heading size="xl" level="2">16</flux:heading>
                    <flux:text size="sm" class="mt-2">Ready to ship</flux:text>
                </div>
                <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
                    <flux:text variant="subtle">Refunds</flux:text>
                    <flux:heading size="xl" level="2">3</flux:heading>
                    <flux:text size="sm" class="mt-2">Last 7 days</flux:text>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex flex-col gap-4 border-b border-zinc-200 p-4 dark:border-zinc-700 md:flex-row md:items-center md:justify-between">
                <div class="w-full md:max-w-sm">
                    <flux:input name="order_search" label="Search" placeholder="Search orders or customers" icon="magnifying-glass" />
                </div>
                <div class="grid w-full gap-3 sm:grid-cols-2 lg:max-w-2xl lg:grid-cols-3">
                    <flux:select name="status" label="Status">
                        <flux:select.option value="all">All statuses</flux:select.option>
                        <flux:select.option value="paid">Paid</flux:select.option>
                        <flux:select.option value="pending">Pending</flux:select.option>
                        <flux:select.option value="refunded">Refunded</flux:select.option>
                    </flux:select>
                    <flux:select name="channel" label="Channel">
                        <flux:select.option value="all">All channels</flux:select.option>
                        <flux:select.option value="online">Online</flux:select.option>
                        <flux:select.option value="store">Retail store</flux:select.option>
                    </flux:select>
                    <flux:select name="sort" label="Sort by">
                        <flux:select.option value="recent">Most recent</flux:select.option>
                        <flux:select.option value="amount">Amount</flux:select.option>
                        <flux:select.option value="status">Status</flux:select.option>
                    </flux:select>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                    <thead class="bg-zinc-50 text-xs uppercase tracking-wide text-zinc-500 dark:bg-zinc-800/60 dark:text-zinc-400">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left font-medium">Order</th>
                            <th scope="col" class="px-4 py-3 text-left font-medium">Customer</th>
                            <th scope="col" class="px-4 py-3 text-left font-medium">Total</th>
                            <th scope="col" class="px-4 py-3 text-left font-medium">Status</th>
                            <th scope="col" class="px-4 py-3 text-left font-medium">Placed</th>
                            <th scope="col" class="px-4 py-3 text-right font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 text-zinc-700 dark:divide-zinc-800 dark:text-zinc-300">
                        <tr>
                            <td class="px-4 py-4 font-semibold text-zinc-900 dark:text-white">#10482</td>
                            <td class="px-4 py-4">Rami Shaw</td>
                            <td class="px-4 py-4">$248.00</td>
                            <td class="px-4 py-4">
                                <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">Paid</span>
                            </td>
                            <td class="px-4 py-4">Dec 12, 2025</td>
                            <td class="px-4 py-4 text-right">
                                <flux:button variant="ghost" size="sm">View</flux:button>
                            </td>
                        </tr>
                        <tr>
                            <td class="px-4 py-4 font-semibold text-zinc-900 dark:text-white">#10479</td>
                            <td class="px-4 py-4">Noah Kim</td>
                            <td class="px-4 py-4">$96.00</td>
                            <td class="px-4 py-4">
                                <span class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-700 dark:bg-amber-500/15 dark:text-amber-300">Pending</span>
                            </td>
                            <td class="px-4 py-4">Dec 12, 2025</td>
                            <td class="px-4 py-4 text-right">
                                <flux:button variant="ghost" size="sm">Review</flux:button>
                            </td>
                        </tr>
                        <tr>
                            <td class="px-4 py-4 font-semibold text-zinc-900 dark:text-white">#10465</td>
                            <td class="px-4 py-4">Carmen Diaz</td>
                            <td class="px-4 py-4">$420.00</td>
                            <td class="px-4 py-4">
                                <span class="inline-flex items-center rounded-full bg-sky-50 px-2.5 py-1 text-xs font-medium text-sky-700 dark:bg-sky-500/15 dark:text-sky-300">Shipped</span>
                            </td>
                            <td class="px-4 py-4">Dec 10, 2025</td>
                            <td class="px-4 py-4 text-right">
                                <flux:button variant="ghost" size="sm">Track</flux:button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
