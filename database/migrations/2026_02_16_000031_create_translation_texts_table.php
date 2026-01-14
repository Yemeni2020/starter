<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('translation_texts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('translation_key_id')->constrained('translation_keys')->cascadeOnDelete();
            $table->string('locale', 10);
            $table->text('text');
            $table->timestamps();

            $table->unique(['translation_key_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('translation_texts');
    }
};
