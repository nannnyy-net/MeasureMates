<?php

namespace App\Http\Controllers;

use App\Services\ConversionService;
use App\Models\ConversionHistory;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ConverterController extends Controller
{
    protected ConversionService $conversionService;

    public function __construct(ConversionService $conversionService)
    {
        $this->conversionService = $conversionService;
    }

    public function convert(Request $request)
    {
        try {
            $allowedUnits = array_keys(config('conversions.volume_units', []));

            $validated = $request->validate([
                'amount' => 'required|numeric|min:0.000001|max:1000000',
                'from_unit' => ['required', 'string', 'in:' . implode(',', $allowedUnits)],
                'to_unit' => ['required', 'string', 'in:' . implode(',', $allowedUnits)],
                'ingredient' => ['nullable', 'string', 'max:255'],
            ], [
                'amount.required' => 'Please enter an amount.',
                'amount.numeric' => 'The amount must be a number.',
                'amount.min' => 'Please enter an amount greater than zero.',
                'amount.max' => 'Please enter a reasonable amount.',
                'from_unit.required' => 'Source unit is required.',
                'from_unit.in' => 'The source unit is not supported.',
                'to_unit.required' => 'Target unit is required.',
                'to_unit.in' => 'The target unit is not supported.',
                'ingredient.string' => 'Ingredient must be text.',
                'ingredient.max' => 'Ingredient name is too long.',
            ]);

            $amount = round((float) $validated['amount'], 6);
            $fromUnit = (string) $validated['from_unit'];
            $toUnit = (string) $validated['to_unit'];
            $ingredient = trim((string) ($validated['ingredient'] ?? '')) ?: null;

            $result = $this->conversionService->getUnitsNeeded($amount, $fromUnit, $toUnit);
            $phrase = $this->conversionService->buildConversionPhrase($amount, $fromUnit, $toUnit);

            // Persist conversion history.
            $historyItem = ConversionHistory::query()->firstOrCreate(
                [

                    'value_entered' => $amount,
                    'from_unit' => $fromUnit,
                    'to_unit' => $toUnit,
                    'ingredient' => $ingredient,
                ],
                [
                    'converted_value' => $result,
                    'result_text' => $phrase,
                ]
            );


            return response()->json([
                'success' => true,
                'result' => $this->conversionService->formatAmount($result),
                'phrase' => $phrase,

                'history_item' => [
                    'id' => $historyItem->id,
                    'value_entered' => $historyItem->value_entered,
                    'from_unit' => $historyItem->from_unit,
                    'to_unit' => $historyItem->to_unit,
                    'converted_value' => $historyItem->converted_value,
                    'result_text' => $historyItem->result_text,
                    'ingredient' => $historyItem->ingredient,
                    'amount' => $historyItem->value_entered,
                    'result' => $historyItem->converted_value,
                    'phrase' => $historyItem->result_text,
                    'created_at' => $historyItem->created_at?->toIso8601String(),
                ],
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred during conversion. Please try again.',
            ], 500);
        }
    }
}
