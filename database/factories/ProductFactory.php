<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $nameEn = $this->faker->unique()->words(3, true);
        $nameAr = $this->randomArabicProductName();
        $slugEn = Str::slug($nameEn);
        $slugAr = $this->slugifyArabic($nameAr);
        $suffix = $this->faker->unique()->numberBetween(100, 9999);
        $slugEn = "{$slugEn}-{$suffix}";
        $slugAr = "{$slugAr}-{$suffix}";

        $price = $this->faker->randomFloat(2, 20, 500);
        $compareAt = $this->faker->boolean(35) ? $price + ($price * $this->faker->randomFloat(2, 0.1, 0.4)) : null;
        $images = $this->imageSet();
        $reviewsCount = $this->faker->numberBetween(0, 250);
        $rating = $reviewsCount > 0 ? $this->faker->randomFloat(1, 3.5, 5) : null;
        $shippingReturns = [
            'Ships in 1-2 business days.',
            'Free 30-day returns on unused items.',
            'Warranty support included.',
        ];
        $features = array_values($this->faker->randomElements($this->featurePool(), $this->faker->numberBetween(3, 6)));
        $summaryEn = $this->faker->sentence(12);
        $summaryAr = $this->randomArabicSummary();
        $descriptionEn = $this->faker->paragraphs(2, true);
        $descriptionAr = $this->randomArabicDescription();

        return [
            'category_id' => Category::factory(),
            'name' => Str::title($nameEn),
            'slug' => $slugEn,
            'summary' => $summaryEn,
            'description' => $descriptionEn,
            'name_translations' => [
                'en' => Str::title($nameEn),
                'ar' => $nameAr,
            ],
            'slug_translations' => [
                'en' => $slugEn,
                'ar' => $slugAr,
            ],
            'summary_translations' => [
                'en' => $summaryEn,
                'ar' => $summaryAr,
            ],
            'description_translations' => [
                'en' => $descriptionEn,
                'ar' => $descriptionAr,
            ],
            'price' => $price,
            'compare_at_price' => $compareAt,
            'rating' => $rating,
            'reviews_count' => $reviewsCount > 0 ? $reviewsCount : null,
            'shipping_returns' => $shippingReturns,
            'sku' => strtoupper(Str::random(8)),
            'stock' => $this->faker->numberBetween(5, 80),
            'reserved_stock' => 0,
            'is_active' => true,
            'status' => 'ACTIVE',
            'weight_grams' => $this->faker->numberBetween(150, 3000),
            'features' => $features,
            'image' => $images[0],
            'gallery' => $images,
            'images' => $images,
        ];
    }

    private function imageSet(): array
    {
        $pool = [
            'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1503602642458-232111445657?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1526170375885-4d8ecf77b99f?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1491553895911-0055eca6402d?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1512436991641-6745cdb1723f?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1483985988355-763728e1935b?auto=format&fit=crop&w=1200&q=80',
        ];

        return array_values($this->faker->randomElements($pool, 3));
    }

    private function featurePool(): array
    {
        return [
            'Premium materials and finish',
            'Lightweight and durable build',
            'Energy efficient performance',
            'Comfort-focused design',
            'Easy setup and maintenance',
            'Compact, space-saving profile',
            'Two-year warranty coverage',
            'Multi-purpose everyday use',
            'Modern minimal silhouette',
            'Improved airflow and cooling',
        ];
    }

    private function randomArabicProductName(): string
    {
        $adjectives = ['فاخر', 'عملي', 'خفيف', 'متين', 'أنيق', 'ذكي', 'مريح', 'صغير'];
        $nouns = ['مصباح', 'حقيبة', 'سماعات', 'ساعة', 'كرسي', 'مكتب', 'زجاجة', 'قميص'];

        return $this->faker->randomElement($adjectives) . ' ' . $this->faker->randomElement($nouns);
    }

    private function randomArabicSummary(): string
    {
        $items = [
            'تصميم أنيق مع أداء عملي يناسب الاستخدام اليومي.',
            'مزيج مثالي بين الجودة العالية والراحة الطويلة.',
            'تفاصيل دقيقة تمنحك تجربة استخدام مميزة.',
            'خامة ممتازة ولمسات عصرية تليق بذوقك.',
        ];

        return $this->faker->randomElement($items);
    }

    private function randomArabicDescription(): string
    {
        $items = [
            'تم تطوير هذا المنتج ليقدم أداء ثابتاً مع مظهر عصري. مناسب للعمل والمنزل مع خامات متينة.',
            'يوفر توازناً بين الراحة والجودة مع تصميم عملي. سهل الاستخدام والتنظيف.',
            'منتج متعدد الاستخدامات بلمسات جمالية بسيطة. مثالي كخيار يومي يعتمد عليه.',
        ];

        return $this->faker->randomElement($items);
    }

    private function slugifyArabic(string $value): string
    {
        $slug = Str::of($value)
            ->lower()
            ->replace(' ', '-')
            ->replaceMatches('/[^\pL\pN\-]+/u', '')
            ->__toString();

        return $slug !== '' ? $slug : Str::random(6);
    }
}
