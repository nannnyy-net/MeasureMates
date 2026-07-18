<?php

namespace App\Services;

class RecipeParserService
{
    /**
     * Parse raw recipe text into structured ingredient arrays.
     */
    public function parseRecipe(string $text): array
    {
        $lines = explode("\n", $text);
        $ingredients = [];
        $displayOrder = 0;

        // Common unit mapping to canonical database unit symbols/keys
        $unitMap = [
            'ml' => 'ml', 'mls' => 'ml', 'milliliter' => 'ml', 'milliliters' => 'ml', 'mililiters' => 'ml', 'mililiter' => 'ml',
            'tsp' => 'tsp', 'tsps' => 'tsp', 'teaspoon' => 'tsp', 'teaspoons' => 'tsp', 't' => 'tsp',
            'tbsp' => 'tbsp', 'tbsps' => 'tbsp', 'tablespoon' => 'tbsp', 'tablespoons' => 'tbsp', 'tb' => 'tbsp', 'tbs' => 'tbsp', 'T' => 'tbsp',
            'floz' => 'floz', 'fl oz' => 'floz', 'fl. oz.' => 'floz', 'fluid ounce' => 'floz', 'fluid ounces' => 'floz', 'oz' => 'floz', 'ounces' => 'floz',
            'cup' => 'cup', 'cups' => 'cup', 'c' => 'cup',
            'pint' => 'pint', 'pints' => 'pint', 'pt' => 'pint', 'pts' => 'pint',
            'quart' => 'quart', 'quarts' => 'quart', 'qt' => 'quart', 'qts' => 'quart',
            'liter' => 'liter', 'liters' => 'liter', 'l' => 'liter', 'L' => 'liter',
            'gallon' => 'gallon', 'gallons' => 'gallon', 'gal' => 'gallon', 'gals' => 'gallon',
        ];

        // Sort unit keys by length descending to match longer multi-word units first
        $unitKeys = array_keys($unitMap);
        usort($unitKeys, function ($a, $b) {
            return strlen($b) <=> strlen($a);
        });

        $unicodeFractions = [
            '½' => 0.5, '⅓' => 0.333333, '⅔' => 0.666667, '¼' => 0.25, '¾' => 0.75,
            '⅕' => 0.2, '⅖' => 0.4, '⅗' => 0.6, '⅘' => 0.8,
            '⅙' => 0.166667, '⅚' => 0.833333,
            '⅛' => 0.125, '⅜' => 0.375, '⅝' => 0.625, '⅞' => 0.875
        ];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            // Attempt to parse quantity at the beginning of the line
            // Matches: integers, decimals, fractions (1/2, 1 1/2), unicode fractions (½, 1½)
            $escapedUnicode = '';
            foreach (array_keys($unicodeFractions) as $char) {
                $escapedUnicode .= preg_quote($char, '/');
            }
            
            $qtyRegex = '/^(?P<qty>\d+(?:[\s-]+\d+\/\d+|\/\d+|\.\d+|[\s-]*[' . $escapedUnicode . '])?|[' . $escapedUnicode . ']|\d+\/\d+)?\s*(?P<rest>.*)$/u';

            $quantity = 1.0;
            $originalUnit = null;
            $ingredientName = $line;

            if (preg_match($qtyRegex, $line, $matches)) {
                $qtyStr = trim($matches['qty'] ?? '');
                $restStr = trim($matches['rest'] ?? '');

                if ($qtyStr !== '') {
                    $quantity = $this->parseFraction($qtyStr, $unicodeFractions);
                    $ingredientName = $restStr;

                    // Now check if $restStr starts with a known unit
                    foreach ($unitKeys as $unitKey) {
                        $pattern = '/^(' . preg_quote($unitKey, '/') . ')(?:\s+|$|\b)/i';
                        if (preg_match($pattern, $restStr, $unitMatches)) {
                            $matchedUnit = strtolower($unitMatches[1]);
                            $originalUnit = $unitMap[$matchedUnit];
                            $ingredientName = trim(substr($restStr, strlen($unitMatches[0])));
                            break;
                        }
                    }
                }
            }

            $ingredients[] = [
                'ingredient_name' => $ingredientName ?: 'Unnamed Ingredient',
                'quantity' => $quantity,
                'original_unit' => $originalUnit,
                'converted_quantity' => null,
                'converted_unit' => null,
                'display_order' => $displayOrder++,
            ];
        }

        return $ingredients;
    }

    protected function parseFraction(string $qtyStr, array $unicodeFractions): float
    {
        $qtyStr = trim($qtyStr);
        if ($qtyStr === '') {
            return 1.0;
        }

        // Single unicode fraction
        if (isset($unicodeFractions[$qtyStr])) {
            return $unicodeFractions[$qtyStr];
        }

        // Unicode fraction embedded (e.g. 1½)
        foreach ($unicodeFractions as $char => $val) {
            if (str_contains($qtyStr, $char)) {
                $parts = explode($char, $qtyStr);
                $intPart = trim($parts[0]);
                $intVal = $intPart !== '' ? (float)$intPart : 0.0;
                return $intVal + $val;
            }
        }

        // Written fraction (e.g. 1/2 or 1 1/2 or 1-1/2)
        if (str_contains($qtyStr, '/')) {
            $qtyStr = str_replace('-', ' ', $qtyStr);
            $parts = preg_split('/\s+/', $qtyStr);
            if (count($parts) === 2) {
                $intVal = (float)$parts[0];
                $fracPart = $parts[1];
            } else {
                $intVal = 0.0;
                $fracPart = $parts[0];
            }

            $fracParts = explode('/', $fracPart);
            if (count($fracParts) === 2 && (float)$fracParts[1] > 0) {
                return $intVal + ((float)$fracParts[0] / (float)$fracParts[1]);
            }
        }

        return (float) $qtyStr;
    }
}
