<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\ConversionHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HistoryControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test deleting a history item.
     */
    public function test_can_delete_history_item(): void
    {
        $item = ConversionHistory::create([
            'from_unit' => 'ml',
            'to_unit' => 'liter',
            'value_entered' => 100.0,
            'converted_value' => 0.1,
            'result_text' => 'You need 100 milliliters to make 0.1 liters',
        ]);


        $response = $this->deleteJson("/history/{$item->id}");
        $response->assertStatus(200);

        $this->assertDatabaseMissing('conversion_history', [

            'id' => $item->id,
        ]);
    }

    /**
     * Test clearing all history.
     */
    public function test_can_clear_all_history(): void
    {
        ConversionHistory::create([
            'from_unit' => 'ml',
            'to_unit' => 'liter',
            'value_entered' => 100.0,
            'converted_value' => 0.1,
            'result_text' => 'phrase 1',
        ]);

        ConversionHistory::create([
            'from_unit' => 'ml',
            'to_unit' => 'liter',
            'value_entered' => 200.0,
            'converted_value' => 0.2,
            'result_text' => 'phrase 2',
        ]);


        $this->assertEquals(2, ConversionHistory::count());

        $response = $this->deleteJson('/history');
        $response->assertStatus(200);

        $this->assertEquals(0, ConversionHistory::count());
    }
}
