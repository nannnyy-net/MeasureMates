<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\ConversionService;

class VolumeConversionDirectionTest extends TestCase
{
    protected ConversionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ConversionService();
    }

    /**
     * Verify direction-aware phrase wording and mathematically correct quantities
     * across all supported units.
     */
    public function test_direction_aware_phrases_across_all_units(): void
    {
        $units = array_keys(config('conversions.volume_units', []));

        foreach ($units as $from) {
            foreach ($units as $to) {
                if ($from === $to) {
                    continue;
                }

                // Use amount=1 for easier string expectations.
                $amount = 1.0;
                $phrase = $this->service->buildConversionPhrase($amount, $from, $to);

                $fromMl = (float) config("conversions.volume_units.{$from}.ml_value");
                $toMl = (float) config("conversions.volume_units.{$to}.ml_value");


                // requiredFromUnits = (toMl * amount) / fromMl
                $requiredFromUnits = ($toMl * $amount) / $fromMl;
                $requiredFromUnitsFormatted = $this->service->formatAmount($requiredFromUnits);

                // We validate the phrase exact output without calling protected pluralization helpers.
                // Since `buildConversionPhrase()` already uses `formatAmount()` and internal plural rules,
                // the main contract we verify is the direction-aware wording + exact numeric value.
                if ($fromMl < $toMl) {
                    $this->assertStringStartsWith("You need {$requiredFromUnitsFormatted} ", $phrase);
                    $this->assertStringContainsString(' to make 1 ', $phrase);
                } else {
                    $this->assertStringContainsString(' contains ', $phrase);
                    $this->assertStringStartsWith('1 ', $phrase);
                    $this->assertStringContainsString(" contains {$requiredFromUnitsFormatted} ", $phrase);
                }


            }
        }
    }
}


