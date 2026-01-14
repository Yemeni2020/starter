@extends('admin.layouts.app')

@section('content')
    <div class="flex w-full flex-1 flex-col gap-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="space-y-1">
                <flux:heading size="xl" level="1">{{ __('admin.translations.title') }}</flux:heading>
                <flux:text>{{ __('admin.translations.subtitle') }}</flux:text>
            </div>
            <div class="flex flex-wrap gap-2">
                <flux:button variant="outline" :href="route('admin.translations.export')">{{ __('admin.translations.export') }}</flux:button>
                <form method="POST" action="{{ route('admin.translations.clear-cache') }}">
                    @csrf
                    <flux:button type="submit" variant="ghost">{{ __('admin.translations.clear_cache') }}</flux:button>
                </form>
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <div class="grid gap-6 lg:grid-cols-[2fr_1fr]">
            <div class="space-y-6">
                <form method="GET" action="{{ route('admin.translations.index') }}" class="flex flex-col gap-3 rounded-xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-700 dark:bg-zinc-900 lg:flex-row lg:items-end">
                    <flux:input
                        name="q"
                        label="{{ __('admin.translations.search_label') }}"
                        value="{{ $search }}"
                        placeholder="{{ __('admin.translations.search_placeholder') }}"
                    />
                    <flux:button variant="outline" type="submit">{{ __('admin.translations.search') }}</flux:button>
                </form>

                <form method="POST" action="{{ route('admin.translations.update') }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    @forelse ($translations as $translation)
                        <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
                            <div class="flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
                                <div>
                                    <div class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ $translation->group }}.{{ $translation->key }}</div>
                                    <div class="text-xs text-zinc-500">{{ __('admin.translations.group') }}: {{ $translation->group }}</div>
                                </div>
                            </div>

                            <div class="mt-4 grid gap-4 md:grid-cols-2">
                                @foreach ($locales as $locale)
                                    @php
                                        $textValue = $translation->texts->firstWhere('locale', $locale)?->text ?? '';
                                    @endphp
                                    <flux:input
                                        name="translations[{{ $translation->id }}][{{ $locale }}]"
                                        label="{{ strtoupper($locale) }}"
                                        value="{{ old("translations.{$translation->id}.{$locale}", $textValue) }}"
                                    />
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div class="rounded-xl border border-zinc-200 bg-white p-6 text-sm text-zinc-600 shadow-xs dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300">
                            {{ __('admin.translations.empty') }}
                        </div>
                    @endforelse

                    @if ($translations->count() > 0)
                        <flux:button type="submit" variant="primary">{{ __('admin.translations.save') }}</flux:button>
                    @endif

                    {{ $translations->links() }}
                </form>
            </div>

            <div class="space-y-6">
                <form method="POST" action="{{ route('admin.translations.store') }}" class="rounded-xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
                    @csrf

                    <flux:heading size="lg" level="2">{{ __('admin.translations.add_key') }}</flux:heading>
                    <div class="mt-4 space-y-4">
                        <flux:input name="group" label="{{ __('admin.translations.group') }}" value="{{ old('group') }}" placeholder="admin" />
                        <flux:input name="key" label="{{ __('admin.translations.key') }}" value="{{ old('key') }}" placeholder="dashboard.title" />
                        <flux:input name="translations[en]" label="{{ __('admin.translations.value_en') }}" value="{{ old('translations.en') }}" />
                        <flux:input name="translations[ar]" label="{{ __('admin.translations.value_ar') }}" value="{{ old('translations.ar') }}" />
                    </div>
                    <div class="mt-4">
                        <flux:button variant="primary" type="submit">{{ __('admin.translations.add') }}</flux:button>
                    </div>
                </form>

                <form method="POST" action="{{ route('admin.translations.import') }}" enctype="multipart/form-data" class="rounded-xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
                    @csrf

                    <flux:heading size="lg" level="2">{{ __('admin.translations.import_title') }}</flux:heading>
                    <div class="mt-4 space-y-4">
                        <flux:input type="file" name="file" label="{{ __('admin.translations.import_file') }}" accept="application/json" />
                    </div>
                    <div class="mt-4">
                        <flux:button variant="outline" type="submit">{{ __('admin.translations.import') }}</flux:button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
