<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $categories = $this->seedCategories();

        $fixedProducts = $this->seedFeaturedProducts($categories);

        $products = Product::factory()
            ->count(max(0, 20 - $fixedProducts->count()))
            ->state(function () use ($categories) {
                return [
                    'category_id' => $categories->random()->id,
                    'is_active' => true,
                    'status' => 'ACTIVE',
                ];
            })
            ->create();

        $products->merge($fixedProducts)->each(function (Product $product) {
            $reviewsCount = random_int(0, 12);

            if ($reviewsCount > 0) {
                ProductReview::factory()
                    ->count($reviewsCount)
                    ->state([
                        'product_id' => $product->id,
                    ])
                    ->create();
            }
        });
    }

    private function seedCategories()
    {
        $items = [
            ['en' => 'Lighting', 'ar' => 'إضاءة'],
            ['en' => 'Home Office', 'ar' => 'مكتب منزلي'],
            ['en' => 'Accessories', 'ar' => 'إكسسوارات'],
            ['en' => 'Audio Gear', 'ar' => 'معدات صوتية'],
            ['en' => 'Lifestyle', 'ar' => 'أسلوب حياة'],
        ];

        $categories = collect();

        foreach ($items as $item) {
            $slugEn = Str::slug($item['en']);
            $slugAr = $this->slugifyArabic($item['ar']);

            $category = Category::updateOrCreate(
                ['slug' => $slugEn],
                [
                    'name' => $item['en'],
                    'is_active' => true,
                    'name_translations' => [
                        'en' => $item['en'],
                        'ar' => $item['ar'],
                    ],
                    'slug_translations' => [
                        'en' => $slugEn,
                        'ar' => $slugAr,
                    ],
                ]
            );

            $categories->push($category);
        }

        return $categories;
    }

    private function seedFeaturedProducts($categories)
    {
        $items = [
            [
                'en' => 'Aurora Desk Lamp',
                'ar' => 'مصباح أورورا للمكتب',
                'summary_en' => 'Warm ambient light with adjustable brightness for focused work.',
                'summary_ar' => 'إضاءة دافئة قابلة للتعديل لراحة العمل والتركيز.',
                'description_en' => 'A compact desk lamp with a soft glow, metal finish, and touch controls.',
                'description_ar' => 'مصباح مكتب صغير بإضاءة ناعمة ولمسة معدنية وتحكم باللمس.',
            ],
            [
                'en' => 'Nimbus Wireless Speaker',
                'ar' => 'سماعة نيمبس اللاسلكية',
                'summary_en' => 'Portable sound with balanced bass and clear vocals.',
                'summary_ar' => 'صوت محمول بجهير متوازن ونقاء في الصوت.',
                'description_en' => 'Lightweight speaker with long battery life and modern fabric texture.',
                'description_ar' => 'سماعة خفيفة بعمر بطارية طويل وملمس قماشي عصري.',
            ],
            [
                'en' => 'Vertex Laptop Stand',
                'ar' => 'حامل لابتوب فيرتكس',
                'summary_en' => 'Ergonomic elevation for better posture and airflow.',
                'summary_ar' => 'رفع مريح لتحسين الجلسة وتدفق الهواء.',
                'description_en' => 'Foldable aluminum stand designed for stability and portability.',
                'description_ar' => 'حامل ألمنيوم قابل للطي بثبات وسهولة حمل.',
            ],
        ];

        $featuresPool = [
            'Premium materials and finish',
            'Lightweight and durable build',
            'Energy efficient performance',
            'Comfort-focused design',
            'Easy setup and maintenance',
            'Compact, space-saving profile',
        ];

        $pool = $this->imagePool();
        $products = collect();

        foreach ($items as $index => $item) {
            $slugEn = Str::slug($item['en']);
            $slugAr = $this->slugifyArabic($item['ar']);
            $images = array_slice(array_merge($pool, $pool), $index, 3);
            $override = [
                'category_id' => $categories->random()->id,
                'name' => $item['en'],
                'slug' => $slugEn,
                'summary' => $item['summary_en'],
                'description' => $item['description_en'],
                'name_translations' => [
                    'en' => $item['en'],
                    'ar' => $item['ar'],
                ],
                'slug_translations' => [
                    'en' => $slugEn,
                    'ar' => $slugAr,
                ],
                'summary_translations' => [
                    'en' => $item['summary_en'],
                    'ar' => $item['summary_ar'],
                ],
                'description_translations' => [
                    'en' => $item['description_en'],
                    'ar' => $item['description_ar'],
                ],
                'features' => array_slice($featuresPool, 0, 3 + ($index % 3)),
                'image' => $images[0],
                'gallery' => $images,
                'images' => $images,
                'is_active' => true,
                'status' => 'ACTIVE',
            ];

            $data = array_merge(Product::factory()->make()->toArray(), $override);

            $products->push(Product::updateOrCreate(['slug' => $slugEn], $data));
        }

        return $products;
    }

    private function imagePool(): array
    {
        return [
            'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1503602642458-232111445657?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1526170375885-4d8ecf77b99f?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1491553895911-0055eca6402d?auto=format&fit=crop&w=1200&q=80',
        ];
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
