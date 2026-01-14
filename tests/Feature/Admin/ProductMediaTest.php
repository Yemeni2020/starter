<?php

namespace Tests\Feature\Admin;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductMediaTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_delete_product_image(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['is_admin' => true]);
        $product = Product::factory()->create();

        $upload = UploadedFile::fake()->image('sample.jpg');

        $this->actingAs($admin)
            ->post(route('admin.products.images.store', $product), [
                'images' => [$upload],
            ])
            ->assertRedirect();

        $media = $product->mediaAssets()->firstOrFail();

        $this->actingAs($admin)
            ->delete(route('admin.products.images.destroy', [$product, $media]))
            ->assertRedirect();

        $this->assertDatabaseMissing('media_assets', ['id' => $media->id]);
        Storage::disk('public')->assertMissing($media->url);
    }
}
