<?php

namespace App\Livewire\Store;

use App\Domain\Catalog\Actions\ShowProductAction;
use App\Models\Product;
use App\Support\LocaleSegment;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class ProductShowAdvanced extends Component
{
    public string $locale = 'en';
    public string $slug = '';

    public array $product = [];
    public array $options = [];
    public array $variants = [];
    public array $media = [];

    public array $selectedOptionValueIds = [];
    public ?int $activeVariantId = null;
    public ?string $activeMediaUrl = null;
    public array $activeMediaList = [];
    public array $activeVariant = [];

    public function mount(string $locale, string $slug, ShowProductAction $action): void
    {
        $this->locale = $locale;
        $this->slug = $slug;

        $requestedLocale = LocaleSegment::normalize($locale);
        $baseLocale = LocaleSegment::base($requestedLocale);

        app()->setLocale($requestedLocale);
        session(['locale' => $requestedLocale]);

        $product = $action->execute($slug);
        $product->loadMissing([
            'category',
            'options.values',
            'variants.optionValues.option',
            'variants.inventoryLevels.location',
            'mediaAssets',
        ]);

        $otherBase = $baseLocale === 'ar' ? 'en' : 'ar';
        $slugCurrent = data_get($product->slug_translations ?? [], $baseLocale);
        $slugOther = data_get($product->slug_translations ?? [], $otherBase);
        if ($slugOther === $slug && $slugCurrent && $slugCurrent !== $slug) {
            $this->redirectRoute('product.show.advanced', [
                'locale' => $otherBase,
                'slug' => $slugOther,
            ]);
            return;
        }

        $this->product = $this->serializeProduct($product);
        $this->options = $this->serializeOptions($product);
        $this->variants = $this->serializeVariants($product);
        $this->media = $this->serializeMedia($product);

        foreach ($this->options as $option) {
            $firstValue = $option['values'][0]['id'] ?? null;
            if ($firstValue) {
                $this->selectedOptionValueIds[$option['code']] = $firstValue;
            }
        }

        $this->setActiveVariant();
        $this->setActiveMedia();
    }

    public function selectOption(string $code, int $valueId): void
    {
        $this->selectedOptionValueIds[$code] = $valueId;
        $this->setActiveVariant();
        $this->setActiveMedia();
    }

    public function selectMedia(string $url): void
    {
        $this->activeMediaUrl = $url;
    }

    private function setActiveVariant(): void
    {
        $selectedIds = array_values(array_filter($this->selectedOptionValueIds));

        $this->activeVariant = [];
        $this->activeVariantId = null;

        foreach ($this->variants as $variant) {
            $variantOptionIds = $variant['option_value_ids'] ?? [];
            $match = !array_diff($selectedIds, $variantOptionIds);

            if ($match) {
                $this->activeVariant = $variant;
                $this->activeVariantId = $variant['id'];
                break;
            }
        }

        if (!$this->activeVariant && !empty($this->variants)) {
            $this->activeVariant = $this->variants[0];
            $this->activeVariantId = $this->variants[0]['id'] ?? null;
        }
    }

    private function setActiveMedia(): void
    {
        $colorValueId = $this->selectedOptionValueIds['color'] ?? null;
        $variantId = $this->activeVariantId;

        $variantMedia = $this->filterMedia(fn ($item) => $item['variant_id'] === $variantId);
        $colorMedia = $this->filterMedia(fn ($item) => $item['variant_id'] === null && $item['option_value_id'] === $colorValueId);
        $genericMedia = $this->filterMedia(fn ($item) => $item['variant_id'] === null && $item['option_value_id'] === null);

        $list = array_values(array_merge($variantMedia, $colorMedia, $genericMedia));
        $seen = [];
        $deduped = [];

        foreach ($list as $item) {
            if (isset($seen[$item['id']])) {
                continue;
            }
            $seen[$item['id']] = true;
            $deduped[] = $item;
        }

        $this->activeMediaList = $deduped;
        $this->activeMediaUrl = $deduped[0]['url'] ?? $this->product['image'] ?? null;
    }

    private function filterMedia(callable $callback): array
    {
        $filtered = array_filter($this->media, $callback);
        usort($filtered, function ($a, $b) {
            $primaryDiff = ($b['is_primary'] ? 1 : 0) <=> ($a['is_primary'] ? 1 : 0);
            if ($primaryDiff !== 0) {
                return $primaryDiff;
            }
            return ($a['position'] ?? 0) <=> ($b['position'] ?? 0);
        });

        return array_values($filtered);
    }

    private function serializeProduct(Product $product): array
    {
        return [
            'id' => $product->id,
            'slug' => $product->slug,
            'name' => $product->name ?? '',
            'category' => $product->category?->name ?? '-',
            'summary' => $product->summary ?? '',
            'description' => $product->description ?? '',
            'features' => is_array($product->features) ? $product->features : [],
            'image' => $this->resolveMediaUrl($product->image),
        ];
    }

    private function serializeOptions(Product $product): array
    {
        $locale = app()->getLocale();

        return $product->options
            ->sortBy('position')
            ->map(function ($option) use ($locale) {
                return [
                    'id' => $option->id,
                    'code' => $option->code,
                    'name' => $option->label($locale),
                    'values' => $option->values->sortBy('position')->map(function ($value) use ($locale) {
                        return [
                            'id' => $value->id,
                            'value' => $value->value,
                            'label' => $value->label($locale),
                            'swatch_hex' => $value->swatch_hex,
                        ];
                    })->values()->all(),
                ];
            })
            ->values()
            ->all();
    }

    private function serializeVariants(Product $product): array
    {
        return $product->variants
            ->map(function ($variant) {
                return [
                    'id' => $variant->id,
                    'sku' => $variant->sku,
                    'currency' => $variant->currency,
                    'price_cents' => $variant->price_cents,
                    'compare_at_cents' => $variant->compare_at_cents,
                    'effective_price_cents' => $variant->effective_price_cents,
                    'available_quantity' => $variant->available_quantity,
                    'track_inventory' => $variant->track_inventory,
                    'allow_backorder' => $variant->allow_backorder,
                    'is_active' => $variant->is_active ?? true,
                    'option_value_ids' => $variant->optionValues->pluck('id')->all(),
                ];
            })
            ->values()
            ->all();
    }

    private function serializeMedia(Product $product): array
    {
        return $product->mediaAssets
            ->map(function ($asset) {
                return [
                    'id' => $asset->id,
                    'url' => $asset->url,
                    'type' => $asset->type,
                    'alt_text' => $asset->alt_text,
                    'position' => $asset->position,
                    'is_primary' => $asset->is_primary,
                    'option_value_id' => $asset->option_value_id,
                    'variant_id' => $asset->variant_id,
                ];
            })
            ->values()
            ->all();
    }

    private function resolveMediaUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
            return $path;
        }
        if (Storage::disk('public')->exists("product/{$path}")) {
            return Storage::url("product/{$path}");
        }
        return Storage::url($path);
    }

    public function render()
    {
        return view('livewire.store.product-show-advanced');
    }
}
