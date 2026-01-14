<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductMediaReorderRequest;
use App\Http\Requests\Admin\ProductMediaUploadRequest;
use App\Models\Category;
use App\Models\Color;
use App\Models\MediaAsset;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('category')->latest()->paginate(12);

        $stats = [
            'total' => Product::count(),
            'active' => Product::where('is_active', true)->count(),
            'low_stock' => Product::where('stock', '<=', 5)->count(),
            'drafts' => Product::where('is_active', false)->count(),
        ];

        return view('admin.products.index', compact('products', 'stats'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $colors = Color::orderBy('name')->get();
        return view('admin.products.create', compact('categories', 'colors'));
    }

    public function store(Request $request)
    {
        $supportedLocales = config('app.supported_locales', ['ar', 'en']);
        $defaultLocale = config('app.locale', 'ar');

        $slugInput = (array) $request->input('slug', []);
        if (empty($slugInput[$defaultLocale])) {
            $defaultNameValue = $request->input("name.{$defaultLocale}", $request->input('name.en', ''));
            $slugInput[$defaultLocale] = Str::slug((string) $defaultNameValue);
            $request->merge(['slug' => $slugInput]);
        }

        $data = $request->validate($this->productValidationRules($defaultLocale));
        $data['name'] = $request->input('name', []);
        $data['slug'] = $request->input('slug', []);
        $translationPayload = $this->prepareTranslationPayload($data, $supportedLocales, $defaultLocale);
        $data = array_merge($data, $translationPayload);

        $data['is_active'] = (bool) $request->input('is_active', true);
        $data['features'] = $this->normalizeFeatures(
            $request->input('features'),
            $data['description'] ?? null,
            5
        );

        $colorIds = $this->sanitizeColorIds($request->input('color_ids', []));
        $data['colors'] = $this->resolveColorLabels($colorIds);

        $product = Product::create($data);
        $product->colorOptions()->sync($colorIds);

        if ($request->hasFile('images')) {
            $this->storeProductImages($product, $request->file('images', []));
            $this->syncProductMediaFields($product);
        }

        return redirect()->route('admin.products.index')->with('status', __('Product created.'));
    }

    public function edit(Product $product)
    {
        $product->load('colorOptions', 'mediaAssets');
        $categories = Category::orderBy('name')->get();
        $colors = Color::orderBy('name')->get();
        return view('admin.products.edit', compact('product', 'categories', 'colors'));
    }

    public function update(Request $request, Product $product)
    {
        $supportedLocales = config('app.supported_locales', ['ar', 'en']);
        $defaultLocale = config('app.locale', 'ar');

        $slugInput = (array) $request->input('slug', []);
        if (empty($slugInput[$defaultLocale])) {
            $defaultNameValue = $request->input("name.{$defaultLocale}", $request->input('name.en', ''));
            $slugInput[$defaultLocale] = Str::slug((string) $defaultNameValue);
            $request->merge(['slug' => $slugInput]);
        }

        $data = $request->validate($this->productValidationRules($defaultLocale, $product));
        $data['name'] = $request->input('name', []);
        $data['slug'] = $request->input('slug', []);
        $translationPayload = $this->prepareTranslationPayload($data, $supportedLocales, $defaultLocale);
        $data = array_merge($data, $translationPayload);

        $data['is_active'] = (bool) $request->input('is_active', true);
        $data['features'] = $this->normalizeFeatures(
            $request->input('features'),
            $data['description'] ?? null,
            5
        );

        $colorIds = $this->sanitizeColorIds($request->input('color_ids', []));
        $data['colors'] = $this->resolveColorLabels($colorIds);

        if ($request->hasFile('images')) {
            $this->storeProductImages($product, $request->file('images', []));
            $this->syncProductMediaFields($product);
        }

        $product->update($data);
        $product->colorOptions()->sync($colorIds);

        return redirect()->route('admin.products.edit', $product)->with('status', __('Product updated.'));
    }

    public function uploadImages(ProductMediaUploadRequest $request, Product $product)
    {
        $this->authorize('update', $product);

        $this->storeProductImages($product, $request->file('images', []));
        $this->syncProductMediaFields($product);

        return redirect()->back()->with('status', __('Images uploaded.'));
    }

    public function deleteImage(Product $product, MediaAsset $media)
    {
        $this->authorize('update', $product);

        if ($media->product_id !== $product->id) {
            abort(404);
        }

        Storage::disk('public')->delete($media->url);
        $media->delete();

        $this->ensurePrimaryMedia($product);
        $this->syncProductMediaFields($product);

        return redirect()->back()->with('status', __('Image removed.'));
    }

    public function reorderImages(ProductMediaReorderRequest $request, Product $product)
    {
        $this->authorize('update', $product);

        $media = MediaAsset::query()
            ->where('product_id', $product->id)
            ->findOrFail($request->input('media_id'));

        $direction = $request->input('direction');
        $neighborQuery = MediaAsset::query()
            ->where('product_id', $product->id)
            ->where('id', '<>', $media->id);

        $neighbor = $direction === 'up'
            ? $neighborQuery->where('position', '<', $media->position)->orderByDesc('position')->first()
            : $neighborQuery->where('position', '>', $media->position)->orderBy('position')->first();

        if (! $neighbor) {
            return redirect()->back();
        }

        DB::transaction(function () use ($media, $neighbor) {
            $current = $media->position;
            $media->update(['position' => $neighbor->position]);
            $neighbor->update(['position' => $current]);
        });

        $this->syncProductMediaFields($product);

        return redirect()->back()->with('status', __('Image order updated.'));
    }

    public function setPrimaryImage(Product $product, MediaAsset $media)
    {
        $this->authorize('update', $product);

        if ($media->product_id !== $product->id) {
            abort(404);
        }

        $product->mediaAssets()->update(['is_primary' => false]);
        $media->update(['is_primary' => true]);
        $this->syncProductMediaFields($product);

        return redirect()->back()->with('status', __('Primary image updated.'));
    }

    private function normalizeFeatures(mixed $featuresInput, ?string $fallbackDescription, int $take = 5): array
    {
        if (is_array($featuresInput)) {
            return collect($featuresInput)->map(fn ($v) => trim((string) $v))->filter()->take($take)->values()->all();
        }

        if (is_string($featuresInput) && trim($featuresInput) !== '') {
            return collect(preg_split('/\r\n|\r|\n/', $featuresInput))
                ->map(fn ($v) => trim((string) $v))
                ->filter()
                ->take($take)
                ->values()
                ->all();
        }

        if (is_string($fallbackDescription) && trim($fallbackDescription) !== '') {
            return collect(preg_split('/\r\n|\r|\n/', $fallbackDescription))
                ->map(fn ($v) => trim((string) $v))
                ->filter()
                ->take($take)
                ->values()
                ->all();
        }

        return [];
    }

    private function storeProductImages(Product $product, array $files): void
    {
        if (empty($files)) {
            return;
        }

        $position = (int) ($product->mediaAssets()->max('position') ?? -1);
        $hasPrimary = $product->mediaAssets()->where('is_primary', true)->exists();

        foreach ($files as $index => $file) {
            $path = Storage::disk('public')->putFile('product', $file);

            MediaAsset::create([
                'product_id' => $product->id,
                'url' => $path,
                'type' => 'image',
                'position' => $position + $index + 1,
                'is_primary' => ! $hasPrimary && $index === 0,
            ]);
        }
    }

    private function syncProductMediaFields(Product $product): void
    {
        $this->ensurePrimaryMedia($product);

        $media = $product->mediaAssets()->orderBy('position')->get();
        if ($media->isEmpty()) {
            $product->update([
                'image' => null,
                'thumbnail' => null,
                'gallery' => null,
                'images' => null,
            ]);
            return;
        }

        $primary = $media->firstWhere('is_primary', true) ?? $media->first();
        $urls = $media->pluck('url')->values()->all();
        $gallery = count($urls) > 1 ? array_slice($urls, 1) : null;

        $product->update([
            'image' => $primary->url,
            'thumbnail' => $primary->url,
            'gallery' => $gallery,
            'images' => $urls,
        ]);
    }

    private function ensurePrimaryMedia(Product $product): void
    {
        if ($product->mediaAssets()->where('is_primary', true)->exists()) {
            return;
        }

        $first = $product->mediaAssets()->orderBy('position')->first();
        if ($first) {
            $first->update(['is_primary' => true]);
        }
    }

    private function sanitizeColorIds(mixed $input): array
    {
        $values = array_filter((array) $input, fn ($value) => $value !== null && $value !== '');
        return array_values(array_map('intval', $values));
    }

    private function resolveColorLabels(array $colorIds): array
    {
        if (empty($colorIds)) {
            return [];
        }

        return Color::query()
            ->whereIn('id', $colorIds)
            ->get()
            ->map(fn (Color $color) => $color->hex ?: $color->name)
            ->filter()
            ->values()
            ->all();
    }

    private function productValidationRules(string $defaultLocale, ?Product $product = null): array
    {
        $slugRule = Rule::unique('products', 'slug');
        if ($product) {
            $slugRule = $slugRule->ignore($product->id);
        }

        return [
            "name.{$defaultLocale}" => ['required', 'string', 'max:255'],
            'name.en' => ['nullable', 'string', 'max:255'],
            "slug.{$defaultLocale}" => ['required', 'string', 'max:255', $slugRule],
            'slug.en' => ['nullable', 'string', 'max:255'],
            "summary.{$defaultLocale}" => ['nullable', 'string', 'max:500'],
            'summary.en' => ['nullable', 'string', 'max:500'],
            "description.{$defaultLocale}" => ['nullable', 'string'],
            'description.en' => ['nullable', 'string'],
            "seo_title.{$defaultLocale}" => ['nullable', 'string', 'max:70'],
            'seo_title.en' => ['nullable', 'string', 'max:70'],
            "seo_description.{$defaultLocale}" => ['nullable', 'string', 'max:160'],
            'seo_description.en' => ['nullable', 'string', 'max:160'],
            "seo_keywords.{$defaultLocale}" => ['nullable', 'string'],
            'seo_keywords.en' => ['nullable', 'string'],
            'features' => ['nullable', 'string'],
            'category_id' => ['required', 'exists:categories,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'compare_at_price' => ['nullable', 'numeric', 'min:0'],
            'sku' => ['required', 'string', 'max:255', Rule::unique('products', 'sku')->ignore($product->id ?? null)],
            'color' => ['nullable', 'string', 'max:60'],
            'stock' => ['required', 'integer', 'min:0'],
            'weight_grams' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'images' => ['nullable', 'array'],
            'images.*' => ['file', 'image', 'max:5120'],
            'color_ids' => ['nullable', 'array'],
            'color_ids.*' => ['integer', 'exists:colors,id'],
        ];
    }

    private function prepareTranslationPayload(array $validated, array $locales, string $defaultLocale): array
    {
        $fields = ['name', 'slug', 'summary', 'description', 'seo_title', 'seo_description', 'seo_keywords'];
        $payload = [];

        foreach ($fields as $field) {
            $translations = $this->collectTranslations($validated[$field] ?? [], $locales);
            $payload["{$field}_translations"] = $translations;
            $fallbackValue = in_array($field, ['summary', 'description'], true) ? null : '';
            $payload[$field] = $this->fallbackTranslation($translations, $defaultLocale, $fallbackValue);
        }

        return $payload;
    }

    private function collectTranslations(array $input, array $locales): array
    {
        $translations = [];
        foreach ($locales as $locale) {
            $translations[$locale] = trim((string) ($input[$locale] ?? ''));
        }
        return $translations;
    }

    private function fallbackTranslation(array $translations, string $defaultLocale, ?string $fallback = ''): ?string
    {
        if (($translations[$defaultLocale] ?? '') !== '') {
            return $translations[$defaultLocale];
        }

        foreach ($translations as $value) {
            if ($value !== '') {
                return $value;
            }
        }

        return $fallback;
    }
}
