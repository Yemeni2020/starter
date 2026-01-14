<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTranslationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_product_with_translated_name(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $category = Category::factory()->create();

        $payload = [
            'name' => ['en' => 'Test Lamp'],
            'slug' => ['en' => 'test-lamp'],
            'summary' => ['en' => 'Short summary'],
            'description' => ['en' => 'Detailed description'],
            'category_id' => $category->id,
            'price' => 199.99,
            'sku' => 'SKU-TEST-001',
            'stock' => 10,
            'color_ids' => [],
            'is_active' => true,
        ];

        $this->actingAs($admin)
            ->post(route('admin.products.store'), $payload)
            ->assertRedirect(route('admin.products.index'));

        $product = Product::firstOrFail();
        $this->assertSame('Test Lamp', $product->name_translations['en'] ?? null);
    }
}
