@extends('admin.layouts.app')

@section('content')
    <div class="flex w-full flex-1 flex-col gap-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="space-y-1">
                <flux:heading size="xl" level="1">Create category</flux:heading>
                <flux:text>Group products to improve navigation and merchandising.</flux:text>
            </div>
            <div class="flex flex-wrap gap-3">
                <flux:button variant="outline" :href="route('admin.categories.index')" wire:navigate>Back to categories</flux:button>
                <flux:button variant="primary" icon="check" icon:variant="outline">Save category</flux:button>
            </div>
        </div>

        <form class="grid gap-6 lg:grid-cols-[2fr_1fr]">
            <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex flex-col gap-4">
                    <flux:heading size="lg" level="2">Category details</flux:heading>
                    <flux:input name="name" label="Category name" placeholder="Lighting" />
                    <flux:input name="slug" label="Slug" placeholder="lighting" />
                    <flux:textarea name="description" label="Description" rows="5" placeholder="Describe this category for internal reference."></flux:textarea>
                    <flux:select name="parent" label="Parent category">
                        <flux:select.option value="">No parent</flux:select.option>
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
                            <flux:select.option value="visible">Visible</flux:select.option>
                            <flux:select.option value="hidden">Hidden</flux:select.option>
                        </flux:select>
                        <flux:checkbox name="featured" label="Show on homepage navigation" />
                    </div>
                </div>
                <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex flex-col gap-4">
                        <flux:heading size="lg" level="2">Cover image</flux:heading>
                        <flux:input type="file" name="image" label="Upload image" />
                        <div class="rounded-xl border border-dashed border-zinc-200 bg-zinc-50 p-6 text-center text-sm text-zinc-500 dark:border-zinc-700 dark:bg-zinc-800/60">
                            Drag and drop or click to upload. Recommended 1200 x 600px.
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
