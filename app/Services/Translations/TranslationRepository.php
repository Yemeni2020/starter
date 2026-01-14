<?php

namespace App\Services\Translations;

use App\Models\TranslationText;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class TranslationRepository
{
    private const CACHE_KEY = 'translations.db';

    public function getTranslationsFor(string $locale, string $group): array
    {
        $all = $this->all();

        return $all[$locale][$group] ?? [];
    }

    public function all(): array
    {
        if (! Schema::hasTable('translation_keys') || ! Schema::hasTable('translation_texts')) {
            return [];
        }

        return Cache::rememberForever(self::CACHE_KEY, function () {
            $rows = TranslationText::query()
                ->select('translation_keys.group', 'translation_keys.key', 'translation_texts.locale', 'translation_texts.text')
                ->join('translation_keys', 'translation_keys.id', '=', 'translation_texts.translation_key_id')
                ->get();

            $translations = [];

            foreach ($rows as $row) {
                $translations[$row->locale][$row->group][$row->key] = $row->text;
            }

            return $translations;
        });
    }

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
