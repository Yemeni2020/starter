<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attribute_definitions', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->json('label_translations')->nullable();
            $table->string('type', 20);
            $table->string('unit')->nullable();
            $table->text('description')->nullable();
            $table->json('options')->nullable();
            $table->boolean('is_variant_specific')->default(false);
            $table->boolean('is_required')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('definition_id')->constrained('attribute_definitions')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->cascadeOnDelete();
            $table->text('text_value')->nullable();
            $table->decimal('number_value', 16, 4)->nullable();
            $table->boolean('boolean_value')->nullable();
            $table->json('json_value')->nullable();
            $table->timestamps();

            $table->unique(['definition_id', 'product_id', 'variant_id'], 'attribute_values_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attribute_values');
        Schema::dropIfExists('attribute_definitions');
    }
};
