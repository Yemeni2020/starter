<?php

namespace App\Services\Seo;

use App\Models\SeoSetting;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class SeoManager
{
    protected array $meta = [];
    protected array $jsonLd = [];

    public function __construct(protected array $defaults = [])
    {
        $this->meta = array_merge([
            'title' => $defaults['site_title'] ?? config('app.name'),
            'description' => $defaults['site_description'] ?? '',
            'canonical' => $defaults['site_url'],
            'locale' => $defaults['locale'] ?? 'en_US',
            'image' => $defaults['default_image'] ?? null,
            'type' => 'website',
        ], $this->meta);
    }

    public function setTitle(string $title): static
    {
        $this->meta['title'] = $title;

        return $this;
    }

    public function setDescription(string $description): static
    {
        $this->meta['description'] = $description;

        return $this;
    }

    public function setCanonical(string $url): static
    {
        $this->meta['canonical'] = $url;

        return $this;
    }

    public function setImage(string $url): static
    {
        $this->meta['image'] = $url;

        return $this;
    }

    public function setType(string $type): static
    {
        $this->meta['type'] = $type;

        return $this;
    }

    public function setLocale(string $locale): static
    {
        $this->meta['locale'] = $locale;

        return $this;
    }

    public function addMeta(string $name, string $content): static
    {
        $this->meta['extras'][$name] = $content;

        return $this;
    }

    public function addJsonLd(array $payload): static
    {
        $this->jsonLd[] = $payload;

        return $this;
    }

    public function meta(): array
    {
        return $this->meta;
    }

    public function jsonLd(): array
    {
        return $this->jsonLd;
    }

    public function render(): string
    {
        $lines = [];

        if ($title = $this->meta['title'] ?? null) {
            $lines[] = '<title>' . e($title) . '</title>';
            $lines[] = '<meta property="og:title" content="' . e($title) . '">';
            $lines[] = '<meta name="twitter:title" content="' . e($title) . '">';
        }

        if ($description = $this->meta['description'] ?? null) {
            $lines[] = '<meta name="description" content="' . e($description) . '">';
            $lines[] = '<meta property="og:description" content="' . e($description) . '">';
            $lines[] = '<meta name="twitter:description" content="' . e($description) . '">';
        }

        if ($canonical = $this->meta['canonical'] ?? null) {
            $lines[] = '<link rel="canonical" href="' . e($canonical) . '">';
            $lines[] = '<meta property="og:url" content="' . e($canonical) . '">';
        }

        if ($image = $this->meta['image'] ?? null) {
            $lines[] = '<meta property="og:image" content="' . e($image) . '">';
            $lines[] = '<meta name="twitter:image" content="' . e($image) . '">';
        }

        $lines[] = '<meta property="og:type" content="' . e($this->meta['type'] ?? 'website') . '">';
        $lines[] = '<meta property="og:locale" content="' . e($this->meta['locale'] ?? 'en_US') . '">';

        if ($twitterCard = Arr::get(config('seo.twitter'), 'card')) {
            $lines[] = '<meta name="twitter:card" content="' . e($twitterCard) . '">';
        }

        if ($twitterSite = Arr::get(config('seo.twitter'), 'site')) {
            $lines[] = '<meta name="twitter:site" content="' . e($twitterSite) . '">';
        }

        if ($appId = Arr::get(config('seo.facebook'), 'app_id')) {
            $lines[] = '<meta property="fb:app_id" content="' . e($appId) . '">';
        }

        foreach ($this->meta['extras'] ?? [] as $name => $content) {
            $lines[] = '<meta name="' . e($name) . '" content="' . e($content) . '">';
        }

        return implode("\n", $lines);
    }

    public function renderJsonLd(): ?string
    {
        if (empty($this->jsonLd)) {
            return null;
        }

        $payload = count($this->jsonLd) === 1 ? $this->jsonLd[0] : $this->jsonLd;

        return '<script type="application/ld+json">' . json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
    }

    public function applySeoSetting(?SeoSetting $setting): static
    {
        if (! $setting) {
            return $this;
        }

        if ($setting->title) {
            $this->setTitle($setting->title);
        }

        if ($setting->description) {
            $this->setDescription($setting->description);
        }

        if ($setting->image) {
            $this->setImage($setting->image);
        }

        if ($setting->locale) {
            $this->setLocale($setting->locale);
        }

        foreach ($setting->meta ?? [] as $name => $value) {
            $this->addMeta($name, $value);
        }

        foreach ($setting->json_ld ?? [] as $payload) {
            $this->addJsonLd($payload);
        }

        return $this;
    }

    public function applyRoute(string $routeName, string $fallbackSlug = 'global'): static
    {
        $setting = SeoSetting::where('route_name', $routeName)->first()
            ?? SeoSetting::where('slug', $fallbackSlug)->first();

        return $this->applySeoSetting($setting);
    }
}
