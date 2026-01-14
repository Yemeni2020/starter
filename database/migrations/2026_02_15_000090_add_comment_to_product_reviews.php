<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_reviews', function (Blueprint $table) {
            if (! Schema::hasColumn('product_reviews', 'comment')) {
                $table->text('comment')->nullable()->after('rating');
            }
        });

        if (Schema::hasColumn('product_reviews', 'comment') && Schema::hasColumn('product_reviews', 'body')) {
            DB::table('product_reviews')->whereNull('comment')->update([
                'comment' => DB::raw('body'),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('product_reviews', function (Blueprint $table) {
            if (Schema::hasColumn('product_reviews', 'comment')) {
                $table->dropColumn('comment');
            }
        });
    }
};
