<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saved_recipes', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->longText('original_recipe');
            $table->longText('converted_recipe');
            $table->string('target_unit');
            $table->timestamps();

            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_recipes');
    }
};
