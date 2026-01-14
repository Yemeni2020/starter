@extends('admin.layouts.app')

@section('content')
    <div class="flex w-full flex-1 flex-col gap-6">
        <div class="flex flex-col gap-3">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="space-y-1">
                    <flux:heading size="xl" level="1">Categories</flux:heading>
                    <flux:text>Organize products into clear groups for your storefront.</flux:text>
                </div>
                <div class="flex flex-wrap gap-3">
                    <flux:button variant="outline" icon="arrow-down-tray" icon:variant="outline">Export</flux:button>
                    <flux:button variant="primary" icon="plus" icon:variant="outline" :href="route('admin.categories.create')" wire:navigate>
                        Add category
                    </flux:button>
                </div>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
                    <flux:text variant="subtle">Total categories</flux:text>
                    <flux:heading size="xl" level="2">24</flux:heading>
                    <flux:text size="sm" class="mt-2">3 new this quarter</flux:text>
                </div>
                <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
                    <flux:text variant="subtle">Visible</flux:text>
                    <flux:heading size="xl" level="2">20</flux:heading>
                    <flux:text size="sm" class="mt-2">4 hidden</flux:text>
                </div>
                <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
                    <flux:text variant="subtle">Avg products</flux:text>
                    <flux:heading size="xl" level="2">32</flux:heading>
                    <flux:text size="sm" class="mt-2">Across active categories</flux:text>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex flex-col gap-4 border-b border-zinc-200 p-4 dark:border-zinc-700 md:flex-row md:items-center md:justify-between">
                <div class="w-full md:max-w-sm">
                    <flux:input name="category_search" label="Search" placeholder="Search categories" icon="magnifying-glass" />
                </div>
                <div class="grid w-full gap-3 sm:grid-cols-2 lg:max-w-xl lg:grid-cols-2">
                    <flux:select name="visibility" label="Visibility">
                        <flux:select.option value="all">All</flux:select.option>
                        <flux:select.option value="visible">Visible</flux:select.option>
                        <flux:select.option value="hidden">Hidden</flux:select.option>
                    </flux:select>
                    <flux:select name="sort" label="Sort by">
                        <flux:select.option value="name">Name</flux:select.option>
                        <flux:select.option value="products">Product count</flux:select.option>
                        <flux:select.option value="updated">Last updated</flux:select.option>
                    </flux:select>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                    <thead class="bg-zinc-50 text-xs uppercase tracking-wide text-zinc-500 dark:bg-zinc-800/60 dark:text-zinc-400">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left font-medium">Category</th>
                            <th scope="col" class="px-4 py-3 text-left font-medium">Products</th>
                            <th scope="col" class="px-4 py-3 text-left font-medium">Status</th>
                            <th scope="col" class="px-4 py-3 text-left font-medium">Updated</th>
                            <th scope="col" class="px-4 py-3 text-right font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 text-zinc-700 dark:divide-zinc-800 dark:text-zinc-300">
                        <tr>
                            <td class="px-4 py-4">
                                <div class="font-semibold text-zinc-900 dark:text-white">Lighting</div>
                                <div class="text-xs text-zinc-500">Ambient and task lighting</div>
                            </td>
                            <td class="px-4 py-4">58</td>
                            <td class="px-4 py-4">
                                <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">Visible</span>
                            </td>
                            <td class="px-4 py-4">Dec 10, 2025</td>
                            <td class="px-4 py-4 text-right">
                                <flux:button variant="ghost" size="sm" :href="route('admin.categories.edit', 'CAT-11')" wire:navigate>Edit</flux:button>
                            </td>
                        </tr>
                        <tr>
                            <td class="px-4 py-4">
                                <div class="font-semibold text-zinc-900 dark:text-white">Decor</div>
                                <div class="text-xs text-zinc-500">Accessories and tabletop</div>
                            </td>
                            <td class="px-4 py-4">41</td>
                            <td class="px-4 py-4">
                                <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">Visible</span>
                            </td>
                            <td class="px-4 py-4">Dec 08, 2025</td>
                            <td class="px-4 py-4 text-right">
                                <flux:button variant="ghost" size="sm" :href="route('admin.categories.edit', 'CAT-08')" wire:navigate>Edit</flux:button>
                            </td>
                        </tr>
                        <tr>
                            <td class="px-4 py-4">
                                <div class="font-semibold text-zinc-900 dark:text-white">Seasonal</div>
                                <div class="text-xs text-zinc-500">Limited drops and campaigns</div>
                            </td>
                            <td class="px-4 py-4">12</td>
                            <td class="px-4 py-4">
                                <span class="inline-flex items-center rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-medium text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300">Hidden</span>
                            </td>
                            <td class="px-4 py-4">Nov 29, 2025</td>
                            <td class="px-4 py-4 text-right">
                                <flux:button variant="ghost" size="sm" :href="route('admin.categories.edit', 'CAT-03')" wire:navigate>Edit</flux:button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
