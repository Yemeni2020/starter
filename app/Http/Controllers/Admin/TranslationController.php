<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ImportTranslationsRequest;
use App\Http\Requests\Admin\StoreTranslationKeyRequest;
use App\Http\Requests\Admin\UpdateTranslationsRequest;
use App\Models\TranslationKey;
use App\Services\Translations\TranslationRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class TranslationController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('q', ''));
        $query = TranslationKey::query()->with('texts');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('group', 'like', "%{$search}%")
                    ->orWhere('key', 'like', "%{$search}%")
                    ->orWhereHas('texts', function ($textQuery) use ($search) {
                        $textQuery->where('text', 'like', "%{$search}%");
                    });
            });
        }

        $translations = $query->orderBy('group')->orderBy('key')->paginate(20)->withQueryString();
        $locales = config('app.supported_locales', ['en', 'ar']);

        return view('admin.translations.index', compact('translations', 'locales', 'search'));
    }

    public function store(StoreTranslationKeyRequest $request, TranslationRepository $repository)
    {
        $data = $request->validated();
        $locales = config('app.supported_locales', ['en', 'ar']);

        $translationKey = TranslationKey::firstOrCreate([
            'group' => $data['group'],
            'key' => $data['key'],
        ]);

        foreach ($locales as $locale) {
            if (! array_key_exists($locale, $data['translations'] ?? [])) {
                continue;
            }

            $translationKey->texts()->updateOrCreate(
                ['locale' => $locale],
                ['text' => trim((string) ($data['translations'][$locale] ?? ''))]
            );
        }

        $repository->clearCache();

        return back()->with('status', __('admin.translations.saved'));
    }

    public function update(UpdateTranslationsRequest $request, TranslationRepository $repository)
    {
        $locales = config('app.supported_locales', ['en', 'ar']);
        $payload = $request->validated()['translations'] ?? [];

        foreach ($payload as $translationKeyId => $values) {
            $translationKey = TranslationKey::find($translationKeyId);

            if (! $translationKey) {
                continue;
            }

            foreach ($locales as $locale) {
                if (! array_key_exists($locale, $values)) {
                    continue;
                }

                $translationKey->texts()->updateOrCreate(
                    ['locale' => $locale],
                    ['text' => trim((string) ($values[$locale] ?? ''))]
                );
            }
        }

        $repository->clearCache();

        return back()->with('status', __('admin.translations.saved'));
    }

    public function export()
    {
        $payload = TranslationKey::query()
            ->with('texts')
            ->orderBy('group')
            ->orderBy('key')
            ->get()
            ->map(function (TranslationKey $translationKey) {
                return [
                    'group' => $translationKey->group,
                    'key' => $translationKey->key,
                    'translations' => $translationKey->texts
                        ->pluck('text', 'locale')
                        ->toArray(),
                ];
            })
            ->values();

        return Response::json($payload)
            ->header('Content-Disposition', 'attachment; filename=translations.json');
    }

    public function import(ImportTranslationsRequest $request, TranslationRepository $repository)
    {
        $locales = config('app.supported_locales', ['en', 'ar']);
        $payload = json_decode($request->file('file')->get(), true);

        if (! is_array($payload)) {
            return back()->withErrors(['file' => __('admin.translations.invalid_file')]);
        }

        DB::transaction(function () use ($payload, $locales) {
            foreach ($payload as $entry) {
                if (! is_array($entry)) {
                    continue;
                }

                $group = trim((string) ($entry['group'] ?? ''));
                $key = trim((string) ($entry['key'] ?? ''));

                if ($group === '' || $key === '') {
                    continue;
                }

                $translationKey = TranslationKey::firstOrCreate([
                    'group' => $group,
                    'key' => $key,
                ]);

                $translations = is_array($entry['translations'] ?? null) ? $entry['translations'] : [];

                foreach ($locales as $locale) {
                    if (! array_key_exists($locale, $translations)) {
                        continue;
                    }

                    $translationKey->texts()->updateOrCreate(
                        ['locale' => $locale],
                        ['text' => trim((string) ($translations[$locale] ?? ''))]
                    );
                }
            }
        });

        $repository->clearCache();

        return back()->with('status', __('admin.translations.imported'));
    }

    public function clearCache(TranslationRepository $repository)
    {
        $repository->clearCache();

        return back()->with('status', __('admin.translations.cache_cleared'));
    }
}
