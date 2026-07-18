<?php

namespace Tests\Feature;

use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecipeControllerTest extends TestCase
{
    use RefreshDatabase;


    /**
     * Test recipe analysis endpoint.
     */
    public function test_recipe_analyze_endpoint(): void
    {
        $response = $this->postJson(route('recipe.analyze'), [
            'recipe_text' => "2 cups flour\n250 mL milk",
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'ingredients');
    }

    /**
     * Test recipe conversion endpoint.
     */
    public function test_recipe_convert_endpoint(): void
    {
        $response = $this->postJson(route('recipe.convert'), [
            // Legacy payload (already-parsed ingredients)
            'ingredients' => [
                [
                    'ingredient_name' => 'flour',
                    'quantity' => 2.0,
                    'original_unit' => 'cup',
                    'display_order' => 0,
                ],
                [
                    'ingredient_name' => 'milk',
                    'quantity' => 250.0,
                    'original_unit' => 'ml',
                    'display_order' => 1,
                ],
            ],
            'target_unit' => 'ml',
            'recipe_name' => 'Test Recipe',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('recipes', [
            'title' => 'Test Recipe',
            'is_saved' => false,
        ]);
    }

    /**
     * Test print log.
     */
    public function test_recipe_print_log(): void
    {
        $response = $this->postJson(route('recipes.print-log'), [
            'item_type' => 'recipe',
            'item_name' => 'Printed Special Recipe',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('print_logs', [
            'item_type' => 'recipe',
            'item_name' => 'Printed Special Recipe',
        ]);
    }
}

