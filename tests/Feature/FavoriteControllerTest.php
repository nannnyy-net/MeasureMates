<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Favorite;
use App\Models\User;
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

        $user = User::factory()->create();

        $this->actingAs($user);

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

    public function test_can_list_favorites_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        Favorite::create([
            'user_id' => $user->id,
            'from_unit' => 'tsp',
            'to_unit' => 'tbsp',
            'amount' => 3.0,
            'ingredient' => null,
        ]);

        $this->actingAs($user);

        $response = $this->getJson('/favorites');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'favorites');
    }

    /**
     * Test deleting a favorite directly by ID.
     */
    public function test_can_delete_favorite_by_id(): void
    {
        $user = User::factory()->create();
        $favorite = Favorite::create([
            'user_id' => $user->id,
            'from_unit' => 'cup',
            'to_unit' => 'liter',
            'amount' => 1.0,
            'ingredient' => null,
        ]);


        $this->actingAs($user);

        $response = $this->deleteJson("/favorites/{$favorite->id}");
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseMissing('favorites', [
            'id' => $favorite->id
        ]);
    }

    public function test_guests_can_save_favorites(): void
    {
        $response = $this->postJson('/favorites', [
            'from_unit' => 'tsp',
            'to_unit' => 'tbsp',
            'amount' => 3.0,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('status', 'added');

        $this->assertDatabaseHas('favorites', [
            'from_unit' => 'tsp',
            'to_unit' => 'tbsp',
            'amount' => 3.0,
            'user_id' => null,
        ]);
    }

    public function test_conversion_status_respects_the_authenticated_user_scope(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Favorite::create([
            'user_id' => $user->id,
            'from_unit' => 'tsp',
            'to_unit' => 'tbsp',
            'amount' => 3.0,
            'ingredient' => null,
        ]);

        $this->actingAs($otherUser);

        $response = $this->postJson('/convert', [
            'from_unit' => 'tsp',
            'to_unit' => 'tbsp',
            'amount' => 3.0,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('is_favorite', false);
    }
}
