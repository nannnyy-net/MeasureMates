<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\IngredientNote;
use Illuminate\Foundation\Testing\RefreshDatabase;

class IngredientNoteControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test storing a new ingredient note.
     */
    public function test_can_create_ingredient_note(): void
    {
        $payload = [
            'ingredient_name' => 'Baking Powder',
            'notes' => 'Store in a cool, dry place. Do not freeze.',
        ];

        $response = $this->postJson('/notes', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Note created successfully!',
            ]);

        $this->assertDatabaseHas('ingredient_notes', $payload);
    }

    /**
     * Test updating an existing ingredient note.
     */
    public function test_can_update_ingredient_note(): void
    {
        $note = IngredientNote::create([
            'ingredient_name' => 'Salt',
            'notes' => 'Kosher salt is less dense than table salt.',
        ]);

        $payload = [
            'ingredient_name' => 'Sea Salt',
            'notes' => 'Sea salt flake size varies widely.',
        ];

        $response = $this->putJson("/notes/{$note->id}", $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Note updated successfully!',
            ]);

        $this->assertDatabaseHas('ingredient_notes', $payload);
    }

    /**
     * Test deleting an ingredient note.
     */
    public function test_can_delete_ingredient_note(): void
    {
        $note = IngredientNote::create([
            'ingredient_name' => 'Milk',
            'notes' => '1 cup of whole milk weighs approximately 244 grams.',
        ]);

        $response = $this->deleteJson("/notes/{$note->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Note deleted successfully!',
            ]);

        $this->assertDatabaseMissing('ingredient_notes', [
            'id' => $note->id
        ]);
    }

    /**
     * Test duplicate note prevention.
     */
    public function test_duplicate_notes_are_rejected(): void
    {
        IngredientNote::create([
            'ingredient_name' => 'Baking Powder',
            'notes' => 'Store in a cool, dry place. Do not freeze.',
        ]);

        $response = $this->postJson('/notes', [
            'ingredient_name' => 'Baking Powder',
            'notes' => 'Store in a cool, dry place. Do not freeze.',
        ]);

        $response->assertStatus(422)
            ->assertJsonFragment([
                'message' => 'A note with the same title and content already exists.',
            ]);
    }

    /**
     * Test note validation constraints.
     */
    public function test_note_validation(): void
    {
        // Missing fields
        $response = $this->postJson('/notes', []);
        $response->assertStatus(422);

        // Name too long
        $response = $this->postJson('/notes', [
            'ingredient_name' => str_repeat('A', 256),
            'notes' => 'Valid notes description',
        ]);
        $response->assertStatus(422);
    }

    public function test_delete_missing_ingredient_note_returns_404_json(): void
    {
        $response = $this->deleteJson('/notes/999999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'The selected note no longer exists.',
            ]);
    }

    public function test_update_missing_ingredient_note_returns_404_json(): void
    {
        $response = $this->putJson('/notes/999999', [
            'ingredient_name' => 'Does Not Exist',
            'notes' => 'No content',
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'The selected note no longer exists.',
            ]);
    }
}
