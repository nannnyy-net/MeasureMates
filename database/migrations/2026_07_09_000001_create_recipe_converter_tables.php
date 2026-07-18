<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create measurement_units table
        if (!Schema::hasTable('measurement_units')) {
            Schema::create('measurement_units', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('symbol');
                $table->string('type')->default('volume');
                $table->double('conversion_factor'); // ml_value equivalent
                $table->timestamps();
            });

            // Seed default volume units
            $volumeUnits = [
                ['name' => 'Milliliter', 'symbol' => 'mL', 'type' => 'volume', 'conversion_factor' => 1.0, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Teaspoon', 'symbol' => 'tsp', 'type' => 'volume', 'conversion_factor' => 4.92892159375, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Tablespoon', 'symbol' => 'tbsp', 'type' => 'volume', 'conversion_factor' => 14.78676478125, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Fluid Ounce', 'symbol' => 'fl oz', 'type' => 'volume', 'conversion_factor' => 29.5735295625, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Cup', 'symbol' => 'cup', 'type' => 'volume', 'conversion_factor' => 236.5882365, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Pint', 'symbol' => 'pt', 'type' => 'volume', 'conversion_factor' => 473.176473015625, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Quart', 'symbol' => 'qt', 'type' => 'volume', 'conversion_factor' => 946.35294603125, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Liter', 'symbol' => 'L', 'type' => 'volume', 'conversion_factor' => 1000.0, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Gallon', 'symbol' => 'gal', 'type' => 'volume', 'conversion_factor' => 3785.411784125, 'created_at' => now(), 'updated_at' => now()],
            ];
            DB::table('measurement_units')->insert($volumeUnits);
        }

        // 2. Create recipes table
        if (!Schema::hasTable('recipes')) {
            Schema::create('recipes', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('category')->nullable();
                $table->integer('servings')->nullable();
                $table->text('notes')->nullable();
                $table->boolean('is_saved')->default(false); // To distinguish saved recipes from history
                $table->timestamps();
            });
        }

        // 3. Create recipe_ingredients table
        if (!Schema::hasTable('recipe_ingredients')) {
            Schema::create('recipe_ingredients', function (Blueprint $table) {
                $table->id();
                $table->foreignId('recipe_id')->constrained('recipes')->cascadeOnDelete();
                $table->string('ingredient_name');
                $table->double('quantity')->nullable();
                $table->string('original_unit')->nullable();
                $table->double('converted_quantity')->nullable();
                $table->string('converted_unit')->nullable();
                $table->integer('display_order')->default(0);
                $table->timestamps();
            });
        }

        // 4. Create recipe_conversions table
        if (!Schema::hasTable('recipe_conversions')) {
            Schema::create('recipe_conversions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('recipe_id')->constrained('recipes')->cascadeOnDelete();
                $table->string('target_unit');
                $table->timestamps();
            });
        }

        // 6. Create print_logs table
        if (!Schema::hasTable('print_logs')) {
            Schema::create('print_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('recipe_id')->nullable()->constrained('recipes')->nullOnDelete();
                $table->string('item_type'); // 'single' or 'recipe'
                $table->string('item_name')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('print_logs');
        Schema::dropIfExists('recipe_conversions');
        Schema::dropIfExists('recipe_favorites');
        Schema::dropIfExists('recipe_ingredients');
        Schema::dropIfExists('recipes');
        Schema::dropIfExists('measurement_units');
    }
};
