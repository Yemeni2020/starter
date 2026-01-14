<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Color;
use App\Models\Product;
use Illuminate\Http\Request;
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

        [$primary, $gallery] = $this->storeImages($request);
        if ($primary !== null) {
            $data['image'] = $primary;
            $data['thumbnail'] = $primary;
            $data['gallery'] = $gallery;

            $allImages = $this->buildImageSet($primary, $gallery);
            if ($allImages !== null) {
                $data['images'] = $allImages;
            }
        }

        $product = Product::create($data);
        $product->colorOptions()->sync($colorIds);

        return redirect()->route('admin.products.index')->with('status', 'Product created.');
    }

    public function edit(Product $product)
    {
        $product->load('colorOptions');
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
            [$primary, $gallery] = $this->storeImages($request);
            $allImages = $this->buildImageSet($primary, $gallery);

            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            foreach (($product->gallery ?? []) as $old) {
                Storage::disk('public')->delete($old);
            }

            $data['image'] = $primary;
            $data['thumbnail'] = $primary;
            $data['gallery'] = $gallery;
            if ($allImages !== null) {
                $data['images'] = $allImages;
            }
        }

        $product->update($data);
        $product->colorOptions()->sync($colorIds);

        return redirect()->route('admin.products.edit', $product)->with('status', 'Product updated.');
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

    private function storeImages(Request $request): array
    {
        $files = $request->file('images', []);

        if (!is_array($files) || count($files) === 0) {
            return [null, null];
        }

        $paths = [];
        foreach ($files as $file) {
            $paths[] = Storage::disk('public')->putFile('product', $file);
        }

        $primary = array_shift($paths);
        $gallery = count($paths) ? $paths : null;

        return [$primary, $gallery];
    }

    private function buildImageSet(?string $primary, ?array $gallery): ?array
    {
        if (!$primary) {
            return null;
        }

        $images = [$primary];
        if (is_array($gallery)) {
            $images = array_merge($images, $gallery);
        }

        return array_values($images);
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
