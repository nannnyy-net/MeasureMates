<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Favorite;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FavoriteControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test adding to favorites.
     */
    public function test_can_toggle_favorite_add_and_remove(): void
    {
        $payload = [
            'from_unit' => 'tsp',
            'to_unit' => 'tbsp',
            'amount' => 3.0,
        ];

        // 1. First request adds it
        $response = $this->postJson('/favorites', $payload);
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status' => 'added',
            ]);

        $this->assertDatabaseHas('favorites', $payload);

        // 2. Second request toggles/removes it
        $response2 = $this->postJson('/favorites', $payload);
        $response2->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status' => 'removed',
            ]);

        $this->assertDatabaseMissing('favorites', $payload);
    }

    /**
     * Test deleting a favorite directly by ID.
     */
    public function test_can_delete_favorite_by_id(): void
    {
        $favorite = Favorite::create([
            'from_unit' => 'cup',
            'to_unit' => 'liter',
            'amount' => 1.0,
        ]);

        $response = $this->deleteJson("/favorites/{$favorite->id}");
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseMissing('favorites', [
            'id' => $favorite->id
        ]);
    }
}
