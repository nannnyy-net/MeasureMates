<?php

namespace App\Http\Controllers;

use App\Models\SavedRecipe;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class SavedRecipeController extends Controller
{



    public function index()
    {
        try {
            $recipes = SavedRecipe::query()
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $recipes,
            ], 200)
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Unable to load saved recipes right now.',
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            if (!$request->isJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid request: expected application/json.',
                ], 415);
            }

            // Important for JSON requests: $request->all() depends on correct parsing.
            $payload = $request->all();

            $payload['title'] = trim((string) ($payload['title'] ?? ''));

            if ($payload['title'] === '') {
                $payload['title'] = $this->inferTitle($payload);
            }

            $request->merge($payload);

            $data = $request->validate([
                'title' => ['required', 'string', 'max:255'],
                'original_recipe' => ['required', 'string'],
                'converted_recipe' => ['required', 'string'],
                'target_unit' => ['required', 'string', 'max:100'],
            ]);

            // Prevent duplicate recipes: same title + original + converted + target_unit.
            // Use a stable application-level signature, then enforce at DB level.
            // firstOrCreate expects the attributes array for lookup.
            // We'll dedupe using the computed signature.
            $signature = $this->dedupeSignature($data);
            $dataWithSignature = $data + ['dedupe_signature' => $signature];


            $recipe = SavedRecipe::firstOrCreate(
                ['dedupe_signature' => $signature],
                $data + ['dedupe_signature' => $signature]
            );


            // Ensure consistent JSON shape for Alpine UI.
            // If firstOrCreate found an existing row, still return it as the "created" result.
            return response()->json([
                'success' => true,
                'message' => 'Recipe saved successfully.',
                'data' => $recipe->fresh(),
            ], 201);

        } catch (ValidationException $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => app()->isLocal() ? $e->getMessage() : 'Unable to save recipe right now.',
            ], 500);
        }
    }


    public function update(Request $request, SavedRecipe $savedRecipe)
    {
        try {
            $payload = $request->all();

            if ($request->has('title')) {
                $payload['title'] = trim((string) ($payload['title'] ?? ''));

                if ($payload['title'] === '') {
                    $payload['title'] = $this->inferTitle($payload);
                }
            }

            $request->merge($payload);

            $data = $request->validate([
                'title' => ['sometimes', 'nullable', 'string', 'max:255'],
                'original_recipe' => ['sometimes', 'nullable', 'string'],
                'converted_recipe' => ['sometimes', 'nullable', 'string'],
                'target_unit' => ['sometimes', 'nullable', 'string', 'max:100'],
            ]);

            $savedRecipe->fill($data);
            $savedRecipe->save();

            return response()->json([
                'success' => true,
                'message' => 'Recipe updated successfully.',
                'data' => $savedRecipe,
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => app()->isLocal() ? $e->getMessage() : 'Unable to update recipe right now.',
            ], 500);
        }
    }

    public function destroy(SavedRecipe $savedRecipe)
    {
        try {
            $savedRecipe->delete();

            return response()->json([
                'success' => true,
                'message' => 'Recipe deleted successfully.',
            ], 200);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => app()->isLocal() ? $e->getMessage() : 'Unable to delete recipe right now.',
            ], 500);
        }
    }

    public function print($id)
    {
        try {
            $savedRecipe = SavedRecipe::findOrFail($id);

            return response()->view('saved-recipes.print', ['recipe' => $savedRecipe]);
        } catch (Throwable $e) {
            report($e);

            abort(500);
        }
    }

    private function dedupeSignature(array $data): string
    {
        // Signature must be deterministic: normalize whitespace to reduce false negatives.
        $normalized = [
            'title' => trim((string) ($data['title'] ?? '')),
            'original_recipe' => $this->normalizeRecipeText((string) ($data['original_recipe'] ?? '')),
            'converted_recipe' => $this->normalizeRecipeText((string) ($data['converted_recipe'] ?? '')),
            'target_unit' => trim((string) ($data['target_unit'] ?? '')),
        ];

        return hash('sha256', json_encode($normalized, JSON_UNESCAPED_UNICODE));
    }

    private function normalizeRecipeText(string $text): string
    {
        // Normalize line endings + trim each line + collapse multiple spaces.
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $lines = array_map(fn ($l) => trim(preg_replace('/\s+/', ' ', $l)), explode("\n", $text));
        return trim(implode("\n", array_filter($lines, fn ($l) => $l !== '')));
    }

    protected function inferTitle(array $payload): string
    {

        $title = trim((string) ($payload['title'] ?? ''));
        if ($title !== '') {
            return $title;
        }

        $source = trim((string) ($payload['original_recipe'] ?? ''));
        if ($source === '') {
            $source = trim((string) ($payload['converted_recipe'] ?? ''));
        }

        $firstLine = trim((string) explode("\n", $source)[0]);

        return $firstLine !== '' ? $firstLine : 'Untitled Recipe';
    }
}
