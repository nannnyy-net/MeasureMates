<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class FavoriteController extends Controller
{
    /**
     * Toggle a favorite conversion.
     */
    public function store(Request $request)
    {
        try {
            $allowedUnits = array_keys(config('conversions.volume_units', []));

            $validated = $request->validate([
                'from_unit' => ['required', 'string', 'in:' . implode(',', $allowedUnits)],
                'to_unit' => ['required', 'string', 'in:' . implode(',', $allowedUnits)],
                'amount' => 'required|numeric|min:0.000001|max:1000000',
            ], [
                'from_unit.in' => 'The source unit is not supported.',
                'to_unit.in' => 'The target unit is not supported.',
                'amount.max' => 'Please enter a reasonable amount.',
            ]);

            $amount = (float) $validated['amount'];
            $fromUnit = (string) $validated['from_unit'];
            $toUnit = (string) $validated['to_unit'];

            $existing = Favorite::query()
                ->where('from_unit', $fromUnit)
                ->where('to_unit', $toUnit)
                ->where('amount', $amount)
                ->first();

            if ($existing) {
                $existing->delete();

                return response()->json([
                    'success' => true,
                    'status' => 'removed',
                    'message' => 'Conversion removed from favorites.',
                ]);
            }

            $favorite = Favorite::create([
                'from_unit' => $fromUnit,
                'to_unit' => $toUnit,
                'amount' => $amount,
            ]);

            return response()->json([
                'success' => true,
                'status' => 'added',
                'message' => 'Conversion added to favorites!',
                'favorite' => $favorite,
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'We could not update favorites right now. Please try again.',
            ], 500);
        }
    }

    /**
     * Remove a favorite conversion directly by ID.
     */
    public function destroy(Favorite $favorite)
    {
        try {
            $favorite->delete();

            return response()->json([
                'success' => true,
                'message' => 'Favorite removed.',
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'We could not remove that favorite right now.',
            ], 500);
        }
    }
}
