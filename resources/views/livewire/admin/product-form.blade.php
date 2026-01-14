<div class="flex w-full flex-1 flex-col gap-6">
    <div class="flex flex-col gap-3">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="space-y-1">
                <flux:heading size="xl" level="1">{{ $product ? 'Edit Product' : 'Create Product' }}</flux:heading>
                <flux:text>Update the core details to get started.</flux:text>
            </div>
            <flux:button variant="outline" :href="route('admin.products.index')" wire:navigate>
                Back to products
            </flux:button>
        </div>
    </div>

    <form wire:submit.prevent="save" class="grid gap-6 lg:grid-cols-[2fr_1fr]">
        <div class="space-y-6">
            <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="lg" level="2">Basics</flux:heading>
                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    <flux:input name="name_ar" label="Name (AR)" wire:model.defer="name_translations.ar" />
                    <flux:input name="name_en" label="Name (EN)" wire:model.defer="name_translations.en" />
                </div>
                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    <flux:input name="price" label="Price" type="number" step="0.01" wire:model.defer="price" />
                    <flux:input name="sku" label="SKU" wire:model.defer="sku" />
                </div>
                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    <flux:input name="stock" label="Stock" type="number" wire:model.defer="stock" />
                    <flux:switch name="is_active" label="Active" wire:model.defer="is_active" />
                </div>
            </div>

            <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="lg" level="2">Summary</flux:heading>
                <div class="mt-4 grid gap-4">
                    <flux:textarea name="summary_ar" label="Summary (AR)" rows="3" wire:model.defer="summary_translations.ar"></flux:textarea>
                    <flux:textarea name="summary_en" label="Summary (EN)" rows="3" wire:model.defer="summary_translations.en"></flux:textarea>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="lg" level="2">Actions</flux:heading>
                <div class="mt-4 flex flex-col gap-3">
                    <flux:button type="submit" variant="primary" class="w-full">Save product</flux:button>
                </div>
            </div>
        </div>
    </form>
</div>
