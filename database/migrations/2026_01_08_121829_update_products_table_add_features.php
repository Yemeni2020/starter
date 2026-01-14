<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'features')) {
                $table->json('features')->nullable()->after('description');
            }

            // Optional: if you might be missing image/gallery in some environments
            if (!Schema::hasColumn('products', 'image')) {
                $table->string('image')->nullable()->after('weight_grams');
            }

            if (!Schema::hasColumn('products', 'gallery')) {
                $table->json('gallery')->nullable()->after('image');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'features')) {
                $table->dropColumn('features');
            }
        });
    }
};
