<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SavedRecipeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_rejects_non_json_requests_with_415(): void
    {
        $response = $this->post('/saved-recipes', [
            'title' => 'Bad',
            'original_recipe' => 'A',
            'converted_recipe' => 'B',
            'target_unit' => 'ml',
        ]);

        $response->assertStatus(415);
    }

    public function test_store_handles_json_content_type(): void
    {
        $response = $this->postJson('/saved-recipes', [
            'title' => 'Chocolate Cake',
            'original_recipe' => '2 cups flour',
            'converted_recipe' => '480 mL flour',
            'target_unit' => 'ml',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true);
    }

    public function test_saved_recipes_crud_flow(): void
    {
        $response = $this->postJson('/saved-recipes', [
            'title' => 'Chocolate Cake',
            'original_recipe' => '2 cups flour',
            'converted_recipe' => '480 mL flour',
            'target_unit' => 'ml',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('saved_recipes', [
            'title' => 'Chocolate Cake',
            'target_unit' => 'ml',
        ]);

        $this->getJson('/saved-recipes')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data');

        $savedRecipeId = $response->json('data.id');

        $this->putJson('/saved-recipes/' . $savedRecipeId, [
            'title' => 'Updated Cake',
            'original_recipe' => '2 cups flour',
            'converted_recipe' => '500 mL flour',
        ])->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->get('/saved-recipes/' . $savedRecipeId . '/print')
            ->assertStatus(200);

        $this->deleteJson('/saved-recipes/' . $savedRecipeId)
            ->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    public function test_store_infers_title_from_recipe_content_when_title_is_missing(): void
    {
        $response = $this->postJson('/saved-recipes', [
            'title' => '',
            'original_recipe' => "Brownie Cake\n2 cups flour",
            'converted_recipe' => "480 mL flour",
            'target_unit' => 'ml',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.title', 'Brownie Cake');

        $this->assertDatabaseHas('saved_recipes', [
            'title' => 'Brownie Cake',
            'target_unit' => 'ml',
        ]);
    }
}

