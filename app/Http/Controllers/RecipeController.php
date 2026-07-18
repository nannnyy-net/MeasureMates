<?php

namespace App\Http\Controllers;

use App\Models\MeasurementUnit;
use App\Models\PrintLog;
use App\Models\Recipe;
use App\Models\RecipeConversion;
use App\Services\ConversionService;
use App\Services\RecipeParserService;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Illuminate\Validation\ValidationException;

class RecipeController extends Controller
{
    protected RecipeParserService $parserService;
    protected ConversionService $conversionService;

    public function __construct(RecipeParserService $parserService, ConversionService $conversionService)

    {
        $this->parserService = $parserService;
        $this->conversionService = $conversionService;
    }

    /**
     * Analyze raw recipe text and return detected ingredients.
     */
    public function analyze(Request $request)
    {
        $request->validate([
            'recipe_text' => 'required|string|max:10000',
        ]);

        $ingredients = $this->parserService->parseRecipe($request->input('recipe_text'));

        return response()->json([
            'success' => true,
            'ingredients' => $ingredients,
        ]);
    }

    /**
     * Convert recipe ingredients into a target unit.
     */
    public function convert(Request $request)
    {
        $allowedUnits = array_keys(config('conversions.volume_units', []));

        $request->validate([
            'recipe_text' => 'nullable|string|max:10000',
            'ingredients' => 'nullable|array',
            'ingredients.*.ingredient_name' => 'required_with:ingredients|string|max:255',
            'ingredients.*.quantity' => 'nullable|numeric|min:0',
            'ingredients.*.original_unit' => 'nullable|string|in:' . implode(',', $allowedUnits),

            'target_unit' => 'required|string|in:' . implode(',', $allowedUnits),

            'recipe_name' => 'nullable|string|max:255',
            'recipe_category' => 'nullable|string|max:255',
            'recipe_servings' => 'nullable|integer|min:1',
            'recipe_notes' => 'nullable|string',
        ]);

        $targetUnit = $request->input('target_unit');

        $ingredientsInput = $request->input('ingredients');
        if (empty($ingredientsInput)) {
            $recipeText = (string) $request->input('recipe_text', '');
            $ingredientsInput = $this->parserService->parseRecipe($recipeText);
        }

        $targetFactor = $this->getConversionFactor($targetUnit);
        if ($targetFactor === null) {
            return response()->json([
                'success' => false,
                'message' => "Target unit '{$targetUnit}' not found in database.",
            ], 422);
        }

        $convertedIngredients = [];
        $convertedLines = [];
        $originalLines = [];
        $unrecognizedCount = 0;

        foreach ($ingredientsInput as $index => $ing) {
            $qty = $ing['quantity'] !== null ? (float) $ing['quantity'] : null;
            $origUnit = $ing['original_unit'] ? trim($ing['original_unit']) : null;
            $name = trim($ing['ingredient_name']);

            $convertedQty = null;
            $convertedUnitResult = null;

            if ($qty !== null && $origUnit !== null) {
                $origFactor = $this->getConversionFactor($origUnit);
                if ($origFactor !== null) {
                    $convertedQty = round(($qty * $origFactor) / $targetFactor, 6);
                    $convertedUnitResult = $targetUnit;
                } else {
                    $unrecognizedCount++;
                }
            } else {
                $unrecognizedCount++;
            }

            $convertedIngredients[] = [
                'ingredient_name' => $name,
                'quantity' => $qty,
                'original_unit' => $origUnit,
                'converted_quantity' => $convertedQty,
                'converted_unit' => $convertedUnitResult,
                'display_order' => $ing['display_order'] ?? $index,
            ];

            $origUnitLabel = $origUnit ? $this->getPluralLabel($origUnit, $qty ?? 1) : '';
            $originalLines[] = ($qty !== null ? $this->conversionService->formatAmount($qty) . ' ' : '') . ($origUnitLabel ? $origUnitLabel . ' ' : '') . $name;

            if ($convertedQty !== null) {
                $convUnitLabel = $this->getPluralLabel($targetUnit, $convertedQty);
                $convertedLines[] = $this->conversionService->formatAmount($convertedQty) . ' ' . $convUnitLabel . ' ' . $name;
            } else {
                $convertedLines[] = ($qty !== null ? $this->conversionService->formatAmount($qty) . ' ' : '') . ($origUnitLabel ? $origUnitLabel . ' ' : '') . $name;
            }
        }

        $recipe = null;
        $formattedOriginal = '';
        $formattedConverted = '';

        $result = 
            
            \Illuminate\Support\Facades\DB::transaction(function () use (
                $request,
                $convertedIngredients,
                $targetUnit,
                $originalLines,
                $convertedLines
            ) {
                $recipe = Recipe::create([
                    'title' => $request->input('recipe_name') ?: 'Converted Recipe',
                    'category' => $request->input('recipe_category'),
                    'servings' => $request->input('recipe_servings'),
                    'notes' => $request->input('recipe_notes'),
                    'is_saved' => false,
                ]);

                foreach ($convertedIngredients as $ing) {
                    $recipe->ingredients()->create($ing);
                }

                RecipeConversion::create([
                    'user_id' => Auth::id(),
                    'recipe_id' => $recipe->id,
                    'target_unit' => $targetUnit,
                ]);

                return [
                    'recipe' => $recipe,
                    'formattedOriginal' => implode("\n", $originalLines),
                    'formattedConverted' => implode("\n", $convertedLines),
                ];
            });

        $recipe = $result['recipe'];
        $formattedOriginal = $result['formattedOriginal'];
        $formattedConverted = $result['formattedConverted'];

        return response()->json([
            'success' => true,
            'recipe_id' => $recipe->id,
            'ingredients' => $convertedIngredients,
            'original_text' => $formattedOriginal,
            'converted_text' => $formattedConverted,
            'unrecognized_count' => $unrecognizedCount,
        ]);
    }



    /**
     * Track a print event.
     */
    public function printLog(Request $request)


    {
        $request->validate([
            'recipe_id' => 'nullable|integer|exists:recipes,id',
            'item_type' => 'required|string|in:single,recipe',
            'item_name' => 'nullable|string|max:255',
        ]);

        $log = PrintLog::create($request->only(['recipe_id', 'item_type', 'item_name']));

        return response()->json([
            'success' => true,
            'print_log_id' => $log->id,
        ]);
    }



    protected function getConversionFactor(string $unitKey): ?float
    {
        $map = [
            'ml' => 'mL',
            'tsp' => 'tsp',
            'tbsp' => 'tbsp',
            'floz' => 'fl oz',
            'cup' => 'cup',
            'pint' => 'pt',
            'quart' => 'qt',
            'liter' => 'L',
            'gallon' => 'gal',
        ];

        $symbol = $map[strtolower($unitKey)] ?? $unitKey;

        $unit = MeasurementUnit::where('symbol', $symbol)
            ->orWhere('name', 'like', $unitKey)
            ->first();

        return $unit ? (float) $unit->conversion_factor : null;
    }

    protected function getPluralLabel(string $unit, float $amount): string
    {
        if (abs($amount - 1.0) < 0.0000001) {
            return match ($unit) {
                'ml' => 'mL',
                'tsp' => 'tsp',
                'tbsp' => 'tbsp',
                'floz' => 'fl oz',
                'cup' => 'cup',
                'pint' => 'pt',
                'quart' => 'qt',
                'liter' => 'L',
                'gallon' => 'gal',
                default => $unit,
            };
        }

        return match ($unit) {
            'floz' => 'fluid ounces',
            'ml' => 'milliliters',
            'tsp' => 'teaspoons',
            'tbsp' => 'tablespoons',
            'cup' => 'cups',
            'liter' => 'liters',
            'pint' => 'pints',
            'quart' => 'quarts',
            'gallon' => 'gallons',
            default => $unit . 's',
        };
    }
}

