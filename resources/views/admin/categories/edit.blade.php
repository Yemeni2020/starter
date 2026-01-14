@extends('admin.layouts.app')

@section('content')
    <div class="flex w-full flex-1 flex-col gap-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 text-xs text-zinc-500">
                    <span class="rounded-full border border-zinc-200 px-2 py-1 dark:border-zinc-700">Category ID: {{ $category }}</span>
                    <span class="rounded-full bg-emerald-50 px-2 py-1 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">Visible</span>
                </div>
                <flux:heading size="xl" level="1">Edit category</flux:heading>
                <flux:text>Adjust taxonomy details and visibility settings.</flux:text>
            </div>
            <div class="flex flex-wrap gap-3">
                <flux:button variant="outline" :href="route('admin.categories.index')" wire:navigate>Back to categories</flux:button>
                <flux:button variant="outline" icon="document-duplicate" icon:variant="outline">Duplicate</flux:button>
                <flux:button variant="primary" icon="check" icon:variant="outline">Update category</flux:button>
            </div>
        </div>

        <form class="grid gap-6 lg:grid-cols-[2fr_1fr]">
            <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex flex-col gap-4">
                    <flux:heading size="lg" level="2">Category details</flux:heading>
                    <flux:input name="name" label="Category name" value="Lighting" />
                    <flux:input name="slug" label="Slug" value="lighting" />
                    <flux:textarea name="description" label="Description" rows="5">Ambient, task, and statement lighting products.</flux:textarea>
                    <flux:select name="parent" label="Parent category">
                        <flux:select.option value="" selected>No parent</flux:select.option>
                        <flux:select.option value="home">Home</flux:select.option>
                        <flux:select.option value="seasonal">Seasonal</flux:select.option>
                    </flux:select>
                </div>
            </div>

            <div class="flex flex-col gap-6">
                <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex flex-col gap-4">
                        <flux:heading size="lg" level="2">Visibility</flux:heading>
                        <flux:select name="status" label="Status">
                            <flux:select.option value="visible" selected>Visible</flux:select.option>
                            <flux:select.option value="hidden">Hidden</flux:select.option>
                        </flux:select>
                        <flux:checkbox name="featured" label="Show on homepage navigation" checked />
                    </div>
                </div>
                <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex flex-col gap-4">
                        <flux:heading size="lg" level="2">Cover image</flux:heading>
                        <div class="relative overflow-hidden rounded-xl border border-zinc-200 bg-zinc-50 p-6 dark:border-zinc-700 dark:bg-zinc-800/60">
                            <x-placeholder-pattern class="absolute inset-0 size-full stroke-zinc-400/25 dark:stroke-white/10" />
                            <div class="relative z-10 text-sm text-zinc-500">Current image</div>
                        </div>
                        <flux:input type="file" name="image" label="Replace image" />
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
