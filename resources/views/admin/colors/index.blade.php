@extends('admin.layouts.app')

@section('content')
    <div class="flex w-full flex-1 flex-col gap-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="space-y-1">
                <flux:heading size="xl" level="1">Colors</flux:heading>
                <flux:text>Build a reusable palette for your catalog items.</flux:text>
            </div>
            <div class="flex flex-wrap gap-3">
                <flux:button variant="primary" icon="plus" icon:variant="outline" :href="route('admin.colors.create')" wire:navigate>
                    Add color
                </flux:button>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-[1fr_auto]">
            <form method="GET" action="{{ route('admin.colors.index') }}" class="w-full">
                <flux:input name="search" label="Search colors" placeholder="Search by name" icon="magnifying-glass" value="{{ $search }}" />
            </form>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex flex-col gap-3 border-b border-zinc-200 p-4 text-sm dark:border-zinc-700">
                @if (session('status'))
                    <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-emerald-700">
                        {{ session('status') }}
                    </div>
                @elseif (session('error'))
                    <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-2 text-amber-700">
                        {{ session('error') }}
                    </div>
                @else
                    <flux:text variant="subtle">{{ $colors->total() }} colors</flux:text>
                @endif
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                    <thead class="bg-zinc-50 text-xs uppercase tracking-wide text-zinc-500 dark:bg-zinc-800/60 dark:text-zinc-400">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left font-medium">Name</th>
                            <th scope="col" class="px-4 py-3 text-left font-medium">Hex</th>
                            <th scope="col" class="px-4 py-3 text-left font-medium">Products</th>
                            <th scope="col" class="px-4 py-3 text-right font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 text-zinc-700 dark:divide-zinc-800 dark:text-zinc-300">
                        @forelse ($colors as $color)
                            <tr>
                                <td class="px-4 py-4">
                                    <div class="font-semibold text-zinc-900 dark:text-white">{{ $color->name }}</div>
                                    <div class="text-xs text-zinc-500">{{ $color->slug }}</div>
                                </td>
                                <td class="px-4 py-4">
                                    @if ($color->hex)
                                        <div class="flex items-center gap-2 text-xs">
                                            <span aria-hidden="true" class="h-4 w-4 rounded-full border" style="background-color: {{ $color->hex }};"></span>
                                            <span>{{ $color->hex }}</span>
                                        </div>
                                    @else
                                        <span class="text-xs text-zinc-500">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4">{{ $color->products_count }}</td>
                                <td class="px-4 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <flux:button variant="ghost" size="sm" :href="route('admin.colors.edit', $color)" wire:navigate>Edit</flux:button>
                                        <form method="POST" action="{{ route('admin.colors.destroy', $color) }}" onsubmit="return confirm('Are you sure you want to delete this color?');">
                                            @csrf
                                            @method('DELETE')
                                            <flux:button variant="ghost" size="sm" type="submit">Delete</flux:button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-sm text-zinc-500">No colors found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-zinc-100 px-4 py-3 dark:border-zinc-800">
                {{ $colors->links() }}
            </div>
        </div>
    </div>
@endsection
