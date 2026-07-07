<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\ConversionHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ConverterControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test successful AJAX conversion.
     */
    public function test_conversion_endpoint_success(): void
    {
        $payload = [
            'amount' => 2.0,
            'from_unit' => 'tbsp',
            'to_unit' => 'cup',
        ];

        $response = $this->postJson('/convert', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'result' => '32',
                'phrase' => 'You need 32 tablespoons to make 2 cups',

            ]);


        // Assert logged to history database
        $this->assertDatabaseHas('conversion_history', [
            'from_unit' => 'tbsp',
            'to_unit' => 'cup',
            'value_entered' => 2.0,
            'converted_value' => 32,
        ]);

    }

    /**
     * Test validation constraints.
     */
    public function test_conversion_endpoint_validation(): void
    {
        // Negative amount
        $payload = [
            'amount' => -1.0,
            'from_unit' => 'tbsp',
            'to_unit' => 'cup',
        ];

        $response = $this->postJson('/convert', $payload);
        $response->assertStatus(422);

        // Missing values
        $payload = [
            'from_unit' => 'tbsp',
        ];

        $response = $this->postJson('/convert', $payload);
        $response->assertStatus(422);
    }

    public function test_homepage_renders_conversion_output_actions(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200)
            ->assertSee('Copy Result')
            ->assertSee('Copy Phrase')
            ->assertSee('Print Result');
    }
}
