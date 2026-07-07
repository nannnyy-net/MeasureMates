<?php

namespace Tests\Feature;

use App\Models\ConversionHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConversionHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_conversion_is_persisted_to_database(): void
    {
        $response = $this->postJson('/convert', [
            'amount' => '2',
            'from_unit' => 'cup',
            'to_unit' => 'ml',
            'ingredient' => 'Milk',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('conversion_history', [
            'from_unit' => 'cup',
            'to_unit' => 'ml',
            'value_entered' => 2.0,
            'ingredient' => 'Milk',
        ]);
    }

    public function test_duplicate_conversions_are_not_created_on_repeated_submission(): void
    {
        $payload = [
            'amount' => '2',
            'from_unit' => 'cup',
            'to_unit' => 'ml',
            'ingredient' => 'Milk',
        ];

        $first = $this->postJson('/convert', $payload);
        $second = $this->postJson('/convert', $payload);

        $first->assertStatus(200);
        $second->assertStatus(200);

        $this->assertSame(1, ConversionHistory::count());
    }
}
