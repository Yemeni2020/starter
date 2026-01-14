<?php

namespace App\Translations;

use App\Services\Translations\TranslationRepository;
use Illuminate\Support\Arr;
use Illuminate\Translation\FileLoader;

class DatabaseTranslationLoader extends FileLoader
{
    public function __construct($files, $path, array $paths = [], private readonly TranslationRepository $repository)
    {
        parent::__construct($files, $path, $paths);
    }

    public function load($locale, $group, $namespace = null): array
    {
        $lines = parent::load($locale, $group, $namespace);

        if ($namespace !== null && $namespace !== '*') {
            return $lines;
        }

        $overrides = $this->repository->getTranslationsFor($locale, $group);

        foreach ($overrides as $key => $value) {
            if ($group === '*') {
                $lines[$key] = $value;
                continue;
            }

            Arr::set($lines, $key, $value);
        }

        return $lines;
    }
}
