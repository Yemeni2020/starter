<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('sku')->unique();
            $table->string('gtin')->nullable();
            $table->string('mpn')->nullable();
            $table->boolean('has_sensor')->default(false);
            $table->string('currency', 3)->default('USD');
            $table->unsignedBigInteger('price_cents')->default(0);
            $table->unsignedBigInteger('compare_at_cents')->nullable();
            $table->unsignedBigInteger('cost_cents')->nullable();
            $table->unsignedBigInteger('sale_cents')->nullable();
            $table->timestamp('sale_starts_at')->nullable();
            $table->timestamp('sale_ends_at')->nullable();
            $table->unsignedInteger('weight_grams')->nullable();
            $table->unsignedInteger('length_mm')->nullable();
            $table->unsignedInteger('width_mm')->nullable();
            $table->unsignedInteger('height_mm')->nullable();
            $table->boolean('track_inventory')->default(true);
            $table->boolean('allow_backorder')->default(false);
            $table->unsignedInteger('low_stock_threshold')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'sku']);
        });

        Schema::create('product_variant_option_value', function (Blueprint $table) {
            $table->foreignId('variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->foreignId('option_value_id')->constrained('product_option_values')->cascadeOnDelete();
            $table->primary(['variant_id', 'option_value_id']);
        });

        Schema::create('media_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('option_value_id')->nullable()->constrained('product_option_values')->nullOnDelete();
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->string('url');
            $table->string('type')->default('image');
            $table->string('alt_text')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_assets');
        Schema::dropIfExists('product_variant_option_value');
        Schema::dropIfExists('product_variants');
    }
};
