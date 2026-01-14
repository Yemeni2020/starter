@extends('admin.layouts.app')

@section('content')
    <div class="flex w-full flex-1 flex-col gap-6">
        <div class="flex flex-col gap-3">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="space-y-1">
                    <flux:heading size="xl" level="1">Customers</flux:heading>
                    <flux:text>Review customer activity, lifetime value, and engagement.</flux:text>
                </div>
                <div class="flex flex-wrap gap-3">
                    <flux:button variant="outline" icon="arrow-down-tray" icon:variant="outline">Export</flux:button>
                    <flux:button variant="primary" icon="plus" icon:variant="outline">Add customer</flux:button>
                </div>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
                    <flux:text variant="subtle">Total customers</flux:text>
                    <flux:heading size="xl" level="2">4,218</flux:heading>
                    <flux:text size="sm" class="mt-2">+6.2% this month</flux:text>
                </div>
                <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
                    <flux:text variant="subtle">New this week</flux:text>
                    <flux:heading size="xl" level="2">84</flux:heading>
                    <flux:text size="sm" class="mt-2">21% from campaigns</flux:text>
                </div>
                <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
                    <flux:text variant="subtle">Repeat rate</flux:text>
                    <flux:heading size="xl" level="2">38%</flux:heading>
                    <flux:text size="sm" class="mt-2">Target 45%</flux:text>
                </div>
                <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
                    <flux:text variant="subtle">VIP segment</flux:text>
                    <flux:heading size="xl" level="2">112</flux:heading>
                    <flux:text size="sm" class="mt-2">Spend over $500</flux:text>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex flex-col gap-4 border-b border-zinc-200 p-4 dark:border-zinc-700 md:flex-row md:items-center md:justify-between">
                <div class="w-full md:max-w-sm">
                    <flux:input name="customer_search" label="Search" placeholder="Search customers" icon="magnifying-glass" />
                </div>
                <div class="grid w-full gap-3 sm:grid-cols-2 lg:max-w-xl lg:grid-cols-2">
                    <flux:select name="segment" label="Segment">
                        <flux:select.option value="all">All</flux:select.option>
                        <flux:select.option value="vip">VIP</flux:select.option>
                        <flux:select.option value="repeat">Repeat</flux:select.option>
                        <flux:select.option value="new">New</flux:select.option>
                    </flux:select>
                    <flux:select name="sort" label="Sort by">
                        <flux:select.option value="recent">Most recent</flux:select.option>
                        <flux:select.option value="spend">Lifetime spend</flux:select.option>
                        <flux:select.option value="orders">Orders</flux:select.option>
                    </flux:select>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                    <thead class="bg-zinc-50 text-xs uppercase tracking-wide text-zinc-500 dark:bg-zinc-800/60 dark:text-zinc-400">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left font-medium">Customer</th>
                            <th scope="col" class="px-4 py-3 text-left font-medium">Email</th>
                            <th scope="col" class="px-4 py-3 text-left font-medium">Orders</th>
                            <th scope="col" class="px-4 py-3 text-left font-medium">Lifetime spend</th>
                            <th scope="col" class="px-4 py-3 text-left font-medium">Status</th>
                            <th scope="col" class="px-4 py-3 text-right font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 text-zinc-700 dark:divide-zinc-800 dark:text-zinc-300">
                        <tr>
                            <td class="px-4 py-4 font-semibold text-zinc-900 dark:text-white">Ava Patel</td>
                            <td class="px-4 py-4">ava.patel@example.com</td>
                            <td class="px-4 py-4">12</td>
                            <td class="px-4 py-4">$1,284.00</td>
                            <td class="px-4 py-4">
                                <span class="inline-flex items-center rounded-full bg-violet-50 px-2.5 py-1 text-xs font-medium text-violet-700 dark:bg-violet-500/15 dark:text-violet-300">VIP</span>
                            </td>
                            <td class="px-4 py-4 text-right">
                                <flux:button variant="ghost" size="sm">View</flux:button>
                            </td>
                        </tr>
                        <tr>
                            <td class="px-4 py-4 font-semibold text-zinc-900 dark:text-white">Leo Morgan</td>
                            <td class="px-4 py-4">leo.morgan@example.com</td>
                            <td class="px-4 py-4">4</td>
                            <td class="px-4 py-4">$312.00</td>
                            <td class="px-4 py-4">
                                <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">Active</span>
                            </td>
                            <td class="px-4 py-4 text-right">
                                <flux:button variant="ghost" size="sm">View</flux:button>
                            </td>
                        </tr>
                        <tr>
                            <td class="px-4 py-4 font-semibold text-zinc-900 dark:text-white">Ella Zhao</td>
                            <td class="px-4 py-4">ella.zhao@example.com</td>
                            <td class="px-4 py-4">1</td>
                            <td class="px-4 py-4">$58.00</td>
                            <td class="px-4 py-4">
                                <span class="inline-flex items-center rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-medium text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300">New</span>
                            </td>
                            <td class="px-4 py-4 text-right">
                                <flux:button variant="ghost" size="sm">View</flux:button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
