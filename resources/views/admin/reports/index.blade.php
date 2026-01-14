@extends('admin.layouts.app')

@section('content')
    <div class="flex w-full flex-1 flex-col gap-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="space-y-1">
                <flux:heading size="xl" level="1">Reports</flux:heading>
                <flux:text>Monitor performance trends and download exports.</flux:text>
            </div>
            <div class="flex flex-wrap gap-3">
                <flux:button variant="outline" icon="arrow-down-tray" icon:variant="outline">Download report</flux:button>
                <flux:button variant="primary" icon="chart-bar" icon:variant="outline">Generate insights</flux:button>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
                <flux:text variant="subtle">Gross revenue</flux:text>
                <flux:heading size="xl" level="2">$82,490</flux:heading>
                <flux:text size="sm" class="mt-2">+12.4% vs last month</flux:text>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
                <flux:text variant="subtle">Net margin</flux:text>
                <flux:heading size="xl" level="2">36.2%</flux:heading>
                <flux:text size="sm" class="mt-2">Target 40%</flux:text>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
                <flux:text variant="subtle">Average order</flux:text>
                <flux:heading size="xl" level="2">$118</flux:heading>
                <flux:text size="sm" class="mt-2">+6.1% increase</flux:text>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
                <flux:text variant="subtle">Conversion rate</flux:text>
                <flux:heading size="xl" level="2">2.9%</flux:heading>
                <flux:text size="sm" class="mt-2">Goal 3.2%</flux:text>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-[2fr_1fr]">
            <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <flux:heading size="lg" level="2">Sales overview</flux:heading>
                        <flux:text size="sm">Daily revenue and order volume.</flux:text>
                    </div>
                    <flux:select name="period" label="Period">
                        <flux:select.option value="30">Last 30 days</flux:select.option>
                        <flux:select.option value="90">Last 90 days</flux:select.option>
                        <flux:select.option value="365">Last year</flux:select.option>
                    </flux:select>
                </div>
                <div class="relative mt-6 h-64 overflow-hidden rounded-xl border border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800/60">
                    <x-placeholder-pattern class="absolute inset-0 size-full stroke-zinc-400/30 dark:stroke-white/10" />
                    <div class="relative z-10 flex h-full items-center justify-center text-sm text-zinc-500">Chart placeholder</div>
                </div>
            </div>

            <div class="flex flex-col gap-6">
                <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
                    <flux:heading size="lg" level="2">Top products</flux:heading>
                    <div class="mt-4 space-y-4 text-sm text-zinc-600 dark:text-zinc-300">
                        <div class="flex items-center justify-between">
                            <span class="font-medium text-zinc-900 dark:text-white">Lumina Desk Lamp</span>
                            <span>$14,280</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="font-medium text-zinc-900 dark:text-white">Arc Wall Shelf</span>
                            <span>$9,110</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="font-medium text-zinc-900 dark:text-white">Breeze Lantern</span>
                            <span>$7,840</span>
                        </div>
                    </div>
                </div>
                <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
                    <flux:heading size="lg" level="2">Channel mix</flux:heading>
                    <div class="mt-4 space-y-3 text-sm text-zinc-600 dark:text-zinc-300">
                        <div class="flex items-center justify-between">
                            <span>Online store</span>
                            <span>72%</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>Retail showroom</span>
                            <span>18%</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>Wholesale</span>
                            <span>10%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
