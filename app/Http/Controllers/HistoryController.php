<?php

namespace App\Http\Controllers;

use App\Models\ConversionHistory;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
    /**
     * Delete a single history item.
     */
    public function destroy(ConversionHistory $history)
    {
        try {
            $history->delete();

            return response()->json([
                'success' => true,
                'message' => 'History item deleted.',
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'We could not delete that history item right now.',
            ], 500);
        }
    }

    /**
     * Delete multiple history items.
     */
    public function bulkDestroy(Request $request)
    {
        try {
            $validated = $request->validate([
                'ids' => ['required', 'array'],
                'ids.*' => ['integer', 'exists:conversion_history,id'],
            ]);

            ConversionHistory::query()->whereIn('id', $validated['ids'])->delete();

            return response()->json([
                'success' => true,
                'message' => 'Selected history items deleted.',
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'We could not delete the selected history items right now.',
            ], 500);
        }
    }

    /**
     * Clear all history items.
     */
    public function clear()
    {
        try {
            ConversionHistory::query()->delete();

            return response()->json([
                'success' => true,
                'message' => 'History cleared successfully.',
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'We could not clear the history right now.',
            ], 500);
        }
    }
}
