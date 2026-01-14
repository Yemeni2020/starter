<?php

namespace App\Services\Settings;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingsManager
{
    public function all(): array
    {
        return Cache::rememberForever('settings.all', function () {
            return Setting::query()
                ->get()
                ->groupBy('group')
                ->map(fn ($items) => $items->pluck('value', 'key')->toArray())
                ->toArray();
        });
    }

    public function get(string $key, mixed $default = null, ?string $group = null): mixed
    {
        $settings = $this->all();

        if ($group !== null) {
            return $settings[$group][$key] ?? $default;
        }

        foreach ($settings as $values) {
            if (array_key_exists($key, $values)) {
                return $values[$key];
            }
        }

        return $default;
    }

    public function group(string $group): array
    {
        return $this->all()[$group] ?? [];
    }

    public function setGroup(string $group, array $values): void
    {
        foreach ($values as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                [
                    'group' => $group,
                    'value' => $value,
                ]
            );
        }

        $this->clearCache();
    }

    public function clearCache(): void
    {
        Cache::forget('settings.all');
    }
}
