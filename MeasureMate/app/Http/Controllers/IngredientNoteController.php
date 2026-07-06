<?php

namespace App\Http\Controllers;

use App\Models\IngredientNote;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class IngredientNoteController extends Controller
{
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'ingredient_name' => 'required|string|max:255',
                'notes' => 'required|string|max:1000',
            ], [
                'ingredient_name.required' => 'Ingredient name is required.',
                'ingredient_name.max' => 'Ingredient name must not exceed 255 characters.',
                'notes.required' => 'Notes content is required.',
                'notes.max' => 'Notes must not exceed 1000 characters.',
            ]);

            $validated['ingredient_name'] = trim((string) $validated['ingredient_name']);
            $validated['notes'] = trim((string) $validated['notes']);

            if ($validated['ingredient_name'] === '' || $validated['notes'] === '') {
                return response()->json([
                    'success' => false,
                    'message' => 'Title and note content cannot be empty.',
                ], 422);
            }

            $duplicateResponse = $this->ensureUniqueNote($validated['ingredient_name'], $validated['notes']);
            if ($duplicateResponse !== null) {
                return $duplicateResponse;
            }

            $note = IngredientNote::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Note created successfully!',
                'note' => $note,
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'We could not save the note right now. Please try again.',
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $note = IngredientNote::find($id);

            if (!$note) {
                return response()->json([
                    'success' => false,
                    'message' => 'The selected note no longer exists.',
                ], 404);
            }

            $validated = $request->validate([
                'ingredient_name' => 'required|string|max:255',
                'notes' => 'required|string|max:1000',
            ], [
                'ingredient_name.required' => 'Ingredient name is required.',
                'ingredient_name.max' => 'Ingredient name must not exceed 255 characters.',
                'notes.required' => 'Notes content is required.',
                'notes.max' => 'Notes must not exceed 1000 characters.',
            ]);

            $validated['ingredient_name'] = trim((string) $validated['ingredient_name']);
            $validated['notes'] = trim((string) $validated['notes']);

            if ($validated['ingredient_name'] === '' || $validated['notes'] === '') {
                return response()->json([
                    'success' => false,
                    'message' => 'Title and note content cannot be empty.',
                ], 422);
            }

            $duplicateResponse = $this->ensureUniqueNote($validated['ingredient_name'], $validated['notes'], $note->id);
            if ($duplicateResponse !== null) {
                return $duplicateResponse;
            }

            $note->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Note updated successfully!',
                'note' => $note,
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'We could not update the note right now. Please try again.',
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $note = IngredientNote::find($id);

            if (!$note) {
                return response()->json([
                    'success' => false,
                    'message' => 'The selected note no longer exists.',
                ], 404);
            }

            $note->delete();

            return response()->json([
                'success' => true,
                'message' => 'Note deleted successfully!',
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'We could not delete the note right now. Please try again.',
            ], 500);
        }
    }

    protected function ensureUniqueNote(string $ingredientName, string $notes, ?int $ignoreId = null): ?\Illuminate\Http\JsonResponse
    {
        $normalizedTitle = trim($ingredientName);
        $normalizedNotes = trim($notes);

        $query = IngredientNote::query()
            ->whereRaw('LOWER(TRIM(ingredient_name)) = ?', [mb_strtolower($normalizedTitle)])
            ->whereRaw('LOWER(TRIM(notes)) = ?', [mb_strtolower($normalizedNotes)]);

        if ($ignoreId !== null) {
            $query->where('id', '!=', $ignoreId);
        }

        if ($query->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'A note with the same title and content already exists.',
            ], 422);
        }

        return null;
    }
}
