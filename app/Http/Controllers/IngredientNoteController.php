<?php

namespace App\Http\Controllers;

use App\Models\IngredientNote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IngredientNoteController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ingredient_name' => ['required', 'string', 'max:255'],
            'notes' => ['required', 'string', 'max:10000'],
            'is_favorite' => ['nullable', 'boolean'],
        ]);

        if ($this->duplicateExists($validated)) {
            return response()->json([
                'success' => false,
                'message' => 'A note with the same title and content already exists.',
            ], 422);
        }

        $note = IngredientNote::create([
            'ingredient_name' => trim((string) $validated['ingredient_name']),
            'notes' => trim((string) $validated['notes']),
            'is_favorite' => (bool) ($validated['is_favorite'] ?? false),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Note created successfully!',
            'data' => $note,
        ], 200);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $note = IngredientNote::query()->find($id);

        if (! $note) {
            return response()->json([
                'success' => false,
                'message' => 'The selected note no longer exists.',
            ], 404);
        }

        $validated = $request->validate([
            'ingredient_name' => ['required', 'string', 'max:255'],
            'notes' => ['required', 'string', 'max:10000'],
            'is_favorite' => ['nullable', 'boolean'],
        ]);

        if ($this->duplicateExists($validated, $note->id)) {
            return response()->json([
                'success' => false,
                'message' => 'A note with the same title and content already exists.',
            ], 422);
        }

        $note->forceFill([
            'ingredient_name' => trim((string) $validated['ingredient_name']),
            'notes' => trim((string) $validated['notes']),
            'is_favorite' => (bool) ($validated['is_favorite'] ?? $note->is_favorite),
        ])->save();

        return response()->json([
            'success' => true,
            'message' => 'Note updated successfully!',
            'data' => $note->fresh(),
        ], 200);
    }

    public function destroy(string $id): JsonResponse
    {
        $note = IngredientNote::query()->find($id);

        if (! $note) {
            return response()->json([
                'success' => false,
                'message' => 'The selected note no longer exists.',
            ], 404);
        }

        $note->delete();

        return response()->json([
            'success' => true,
            'message' => 'Note deleted successfully!',
        ], 200);
    }

    protected function duplicateExists(array $validated, ?int $excludeId = null): bool
    {
        $query = IngredientNote::query()
            ->where('ingredient_name', trim((string) ($validated['ingredient_name'] ?? '')))
            ->where('notes', trim((string) ($validated['notes'] ?? '')));

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}
