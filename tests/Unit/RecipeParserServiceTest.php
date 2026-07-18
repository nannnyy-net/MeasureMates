<?php

namespace Tests\Unit;

use App\Services\RecipeParserService;
use PHPUnit\Framework\TestCase;

class RecipeParserServiceTest extends TestCase
{
    protected RecipeParserService $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new RecipeParserService();
    }

    /**
     * Test parsing standard lines.
     */
    public function test_parses_standard_ingredients(): void
    {
        $text = "2 cups flour\n250 mL milk\n1 tbsp vanilla\n2 tsp salt";
        $results = $this->parser->parseRecipe($text);

        $this->assertCount(4, $results);

        $this->assertEquals(2.0, $results[0]['quantity']);
        $this->assertEquals('cup', $results[0]['original_unit']);
        $this->assertEquals('flour', $results[0]['ingredient_name']);

        $this->assertEquals(250.0, $results[1]['quantity']);
        $this->assertEquals('ml', $results[1]['original_unit']);
        $this->assertEquals('milk', $results[1]['ingredient_name']);

        $this->assertEquals(1.0, $results[2]['quantity']);
        $this->assertEquals('tbsp', $results[2]['original_unit']);
        $this->assertEquals('vanilla', $results[2]['ingredient_name']);

        $this->assertEquals(2.0, $results[3]['quantity']);
        $this->assertEquals('tsp', $results[3]['original_unit']);
        $this->assertEquals('salt', $results[3]['ingredient_name']);
    }

    /**
     * Test parsing fractions.
     */
    public function test_parses_fractions(): void
    {
        $text = "1 1/2 cups sugar\n1½ cups sugar\n½ cup butter\n0.5 cup butter";
        $results = $this->parser->parseRecipe($text);

        $this->assertCount(4, $results);

        $this->assertEquals(1.5, $results[0]['quantity']);
        $this->assertEquals(1.5, $results[1]['quantity']);
        $this->assertEquals(0.5, $results[2]['quantity']);
        $this->assertEquals(0.5, $results[3]['quantity']);
    }

    /**
     * Test parsing ingredients with no quantities or units.
     */
    public function test_parses_non_standard_lines(): void
    {
        $text = "Chocolate Cake\npinch of salt\n2 eggs";
        $results = $this->parser->parseRecipe($text);

        $this->assertCount(3, $results);

        // No quantity or unit
        $this->assertEquals(1.0, $results[0]['quantity']);
        $this->assertNull($results[0]['original_unit']);
        $this->assertEquals('Chocolate Cake', $results[0]['ingredient_name']);

        $this->assertEquals(1.0, $results[1]['quantity']);
        $this->assertNull($results[1]['original_unit']);
        $this->assertEquals('pinch of salt', $results[1]['ingredient_name']);

        // Quantity but no unit
        $this->assertEquals(2.0, $results[2]['quantity']);
        $this->assertNull($results[2]['original_unit']);
        $this->assertEquals('eggs', $results[2]['ingredient_name']);
    }
}
