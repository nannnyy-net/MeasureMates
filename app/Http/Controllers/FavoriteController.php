<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class FavoriteController extends Controller
{
    /**
     * List favorites for the authenticated user.
     */
    public function index()
    {
        $favorites = collect();

        if (Auth::check()) {
            $favorites = Favorite::query()
                ->where('user_id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            $favorites = Favorite::query()
                ->whereNull('user_id')
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return response()->json([
            'success' => true,
            'favorites' => $favorites,
        ]);
    }

    /**
     * Toggle a favorite conversion.
     */
    public function store(Request $request)
    {
        try {
            $allowedUnits = array_keys(config('conversions.volume_units', []));

            $userId = Auth::check() ? Auth::id() : null;

            $validated = $request->validate([
                'from_unit' => ['required', 'string', 'in:' . implode(',', $allowedUnits)],
                'to_unit' => ['required', 'string', 'in:' . implode(',', $allowedUnits)],
                'amount' => 'required|numeric|min:0.000001|max:1000000',
                'ingredient' => ['nullable', 'string', 'max:255'],
            ], [
                'from_unit.in' => 'The source unit is not supported.',
                'to_unit.in' => 'The target unit is not supported.',
                'amount.max' => 'Please enter a reasonable amount.',
                'ingredient.max' => 'Ingredient is too long.',
            ]);

            $amount = (float) number_format((float) $validated['amount'], 6, '.', '');
            $fromUnit = (string) $validated['from_unit'];
            $toUnit = (string) $validated['to_unit'];
            $ingredient = array_key_exists('ingredient', $validated)
                ? (trim((string) ($validated['ingredient'] ?? '')) !== '' ? trim((string) $validated['ingredient']) : null)
                : null;

            $favoriteKey = Favorite::buildFavoriteKey($userId, $fromUnit, $toUnit, $amount, $ingredient);

            $existing = Favorite::query()
                ->when($userId !== null, function ($query) use ($userId): void {
                    $query->where('user_id', $userId);
                }, function ($query): void {
                    $query->whereNull('user_id');
                })
                ->where('favorite_key', $favoriteKey)
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
                'user_id' => $userId,
                'from_unit' => $fromUnit,
                'to_unit' => $toUnit,
                'amount' => $amount,
                'ingredient' => $ingredient,
                'favorite_key' => $favoriteKey,
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
            if (Auth::check() && (int) $favorite->user_id !== (int) Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to remove this favorite.',
                ], 403);
            }

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
