<div class="space-y-2">
    <label class="text-sm font-medium text-zinc-800 dark:text-zinc-200">
        Color options <span class="text-rose-500">*</span>
    </label>

    <div class="relative" data-multi-dropdown>
        <button type="button"
            class="w-full rounded-xl border border-zinc-200 bg-white px-3 py-2 text-left text-sm shadow-sm
                focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500
                dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-50"
            data-dropdown-trigger>
            <span class="text-zinc-500 dark:text-zinc-400" data-dropdown-placeholder>
                Select colors.
            </span>

            <span class="hidden flex flex-wrap gap-1" data-dropdown-chips></span>

            <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-zinc-400">
                ?
            </span>
        </button>

        <div class="absolute z-30 mt-2 hidden w-full overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-lg
            dark:border-zinc-800 dark:bg-zinc-950"
            data-dropdown-panel>
            <div class="max-h-64 overflow-auto p-2">
                @forelse ($colors as $color)
                    <label
                        class="flex cursor-pointer items-center gap-3 rounded-xl px-3 py-2 hover:bg-zinc-50 dark:hover:bg-zinc-900">
                        <input type="checkbox"
                            class="h-4 w-4 rounded border-zinc-300 text-blue-600 focus:ring-blue-500/20 dark:border-zinc-700"
                            data-color-checkbox value="{{ $color->id }}"
                            data-color-name="{{ $color->name }}"
                            @checked(in_array($color->id, $selectedColorIds)) />
                        <span class="flex items-center gap-2">
                            @if ($color->hex)
                                <span
                                    class="h-4 w-4 rounded-full border border-zinc-200 dark:border-zinc-700"
                                    style="background: {{ $color->hex }}"></span>
                            @endif
                            <span class="text-sm text-zinc-800 dark:text-zinc-100">
                                {{ $color->name }}
                                @if ($color->hex)
                                    <span class="text-xs text-zinc-500 dark:text-zinc-400">({{ $color->hex }})</span>
                                @endif
                            </span>
                        </span>
                    </label>
                @empty
                    <div class="px-3 py-2 text-sm text-zinc-500 dark:text-zinc-400">
                        No colors defined yet
                    </div>
                @endforelse
            </div>

            <div
                class="flex items-center justify-between gap-2 border-t border-zinc-100 p-2 dark:border-zinc-800">
                <button type="button"
                    class="rounded-lg px-3 py-1.5 text-xs font-semibold text-zinc-600 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-900"
                    data-clear>
                    Clear
                </button>
                <button type="button"
                    class="rounded-lg bg-zinc-900 px-3 py-1.5 text-xs font-semibold text-white dark:bg-white dark:text-zinc-900"
                    data-done>
                    Done
                </button>
            </div>
        </div>

        <div data-hidden-inputs></div>

        @error('color_ids')
            <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
        @enderror

        <p class="text-xs text-zinc-500 dark:text-zinc-400">
            Select at least one color (required).
        </p>
    </div>
</div>
