<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\IngredientNote;
use App\Models\ConversionHistory;
use App\Models\Favorite;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed default User when the table is available.
        if (Schema::hasTable('users')) {
            User::factory()->create([
                'name' => 'MeasureMate Chef',
                'email' => 'chef@measuremate.com',
            ]);
        }

        // Seed Ingredient Notes
        IngredientNote::create([
            'ingredient_name' => 'All-Purpose Flour',
            'notes' => 'Always sift flour before measuring to avoid packing, which can add up to 20% more weight than intended.',
        ]);

        IngredientNote::create([
            'ingredient_name' => 'Brown Sugar',
            'notes' => 'Pack firmly into the measuring cup so that it holds its shape when turned out.',
        ]);

        IngredientNote::create([
            'ingredient_name' => 'Active Dry Yeast',
            'notes' => '1 packet of active dry yeast equals 2.25 teaspoons (11 mL) and should be bloomed in warm liquid.',
        ]);

        // Seed Conversion Histories
        ConversionHistory::create([
            'value_entered' => 1.0,
            'from_unit' => 'cup',
            'to_unit' => 'tbsp',
            'converted_value' => 16.0,
            'result_text' => 'You need 16 tablespoons to make 1 cup',
            'ingredient' => 'Flour',
        ]);

        ConversionHistory::create([
            'value_entered' => 2.0,
            'from_unit' => 'tbsp',
            'to_unit' => 'tsp',
            'converted_value' => 6.0,
            'result_text' => 'You need 6 teaspoons to make 2 tablespoons',
            'ingredient' => 'Yeast',
        ]);

        ConversionHistory::create([
            'value_entered' => 500.0,
            'from_unit' => 'ml',
            'to_unit' => 'liter',
            'converted_value' => 0.5,
            'result_text' => 'You need 500 milliliters to make 0.5 liters',
            'ingredient' => 'Water',
        ]);

        // Seed Favorites
        Favorite::create([
            'from_unit' => 'tbsp',
            'to_unit' => 'cup',
            'amount' => 1.0,
        ]);

        Favorite::create([
            'from_unit' => 'tsp',
            'to_unit' => 'tbsp',
            'amount' => 1.0,
        ]);

        Favorite::create([
            'from_unit' => 'cup',
            'to_unit' => 'liter',
            'amount' => 1.0,
        ]);
    }
}
