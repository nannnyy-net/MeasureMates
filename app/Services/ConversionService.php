<?php

namespace App\Services;

class ConversionService
{
    protected array $units;

    public function __construct()
    {
        $this->units = config('conversions.volume_units', []);
    }

    /**
     * Convert an amount from one unit to another via mL.
     */
    public function convert(float $amount, string $fromUnit, string $toUnit): float
    {
        $this->assertValidUnits($fromUnit, $toUnit);

        $fromMl = (float) $this->units[$fromUnit]['ml_value'];
        $toMl = (float) $this->units[$toUnit]['ml_value'];

        return $this->normalizeValue(($amount * $fromMl) / $toMl);
    }

    /**
     * Get units of from_unit needed to make target amount of to_unit.
     * Formula: (to_unit_ml * amount) / from_unit_ml
     */
    public function getUnitsNeeded(float $amount, string $fromUnit, string $toUnit): float
    {
        $this->assertValidUnits($fromUnit, $toUnit);

        $fromMl = (float) $this->units[$fromUnit]['ml_value'];
        $toMl = (float) $this->units[$toUnit]['ml_value'];

        return $this->normalizeValue(($toMl * $amount) / $fromMl);
    }

    /**
     * Format a float number nicely, removing trailing decimals if they are zero.
     */
    public function formatAmount(float $amount, int $precision = 4): string
    {
        $normalized = $this->normalizeValue($amount);

        if (abs($normalized - round($normalized)) < 1e-10) {
            return (string) (int) round($normalized);
        }

        $formatted = number_format($normalized, $precision, '.', '');
        $formatted = rtrim(rtrim($formatted, '0'), '.');

        return $formatted !== '' ? $formatted : '0';
    }

    /**
     * Build the human-readable conversion phrase.
     *
     * Intent:
     * - If FROM unit is smaller than TO unit (ml(from) < ml(to)):
     *   "You need X [from] to make Y [to]"
     * - If FROM unit is larger than TO unit (ml(from) > ml(to)):
     *   "Y [from] contains X [to]"
     */
    public function buildConversionPhrase(float $amount, string $fromUnit, string $toUnit): string
    {
        $this->assertValidUnits($fromUnit, $toUnit);

        $fromMl = (float) $this->units[$fromUnit]['ml_value'];
        $toMl = (float) $this->units[$toUnit]['ml_value'];

        if ($fromMl <= 0.0 || $toMl <= 0.0) {
            throw new \InvalidArgumentException('Invalid unit size configuration.');
        }

        // X = requiredUnits, per requirement:
        // requiredUnits = (targetAmount × targetUnitML) ÷ sourceUnitML
        // Here, source is FROM unit, target is TO unit.
        $requiredFromUnits = $this->getUnitsNeeded($amount, $fromUnit, $toUnit);

        $formattedAmount = $this->formatAmount($amount);
        $formattedRequiredFrom = $this->formatAmount($requiredFromUnits);

        if ($fromMl < $toMl) {
            // You need X FROM to make Y TO
            $fromLabel = $this->getPluralLabel($fromUnit, $requiredFromUnits);
            $toLabel = $this->getPluralLabel($toUnit, $amount);

            return "You need {$formattedRequiredFrom} {$fromLabel} to make {$formattedAmount} {$toLabel}";
        }

        if ($fromMl > $toMl) {
            // Y FROM contains X TO
            // requiredFromUnits is X (FROM units needed to make Y TO). For this direction,
            // we want to say: amount FROM contains requiredFromUnits TO.
            $fromLabel = $this->getPluralLabel($fromUnit, $amount);
            $toLabel = $this->getPluralLabel($toUnit, $requiredFromUnits);

            return "{$formattedAmount} {$fromLabel} contains {$formattedRequiredFrom} {$toLabel}";
        }

        // Equal size units: FROM and TO are interchangeable.
        $fromLabel = $this->getPluralLabel($fromUnit, $amount);
        $toLabel = $this->getPluralLabel($toUnit, $amount);

        if ($amount == 1.0) {
            return "1 {$fromLabel} equals 1 {$toLabel}";
        }

        return "{$formattedAmount} {$fromLabel} equals {$formattedAmount} {$toLabel}";
    }



    protected function getPluralLabel(string $unit, float $amount): string
    {
        $name = $this->units[$unit]['name'];
        if (abs($amount - 1.0) < 0.0000001) {
            return strtolower($name);
        }

        switch ($unit) {
            case 'floz':
                return 'fluid ounces';
            case 'ml':
                return 'milliliters';
            case 'tsp':
                return 'teaspoons';
            case 'tbsp':
                return 'tablespoons';
            case 'cup':
                return 'cups';
            case 'liter':
                return 'liters';
            case 'pint':
                return 'pints';
            case 'quart':
                return 'quarts';
            case 'gallon':
                return 'gallons';
            default:
                return strtolower($name) . 's';
        }
    }

    protected function normalizeValue(float $value): float
    {
        $rounded = round($value, 12);

        return abs($rounded) < 1e-12 ? 0.0 : $rounded;
    }

    protected function assertValidUnits(string $fromUnit, string $toUnit): void
    {
        if (!isset($this->units[$fromUnit]) || !isset($this->units[$toUnit])) {
            throw new \InvalidArgumentException("Invalid conversion units: {$fromUnit} or {$toUnit}");
        }
    }

    public function getUnits(): array
    {
        return $this->units;
    }
}
