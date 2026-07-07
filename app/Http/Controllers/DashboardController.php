<?php

namespace App\Http\Controllers;

use App\Models\ConversionHistory;
use App\Models\Favorite;
use App\Models\IngredientNote;
use App\Services\ConversionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    protected ConversionService $conversionService;

    public function __construct(ConversionService $conversionService)
    {
        $this->conversionService = $conversionService;
    }

    public function index(Request $request)
    {
        $units = $this->conversionService->getUnits();
        $notes = IngredientNote::query()->orderByDesc('is_favorite')->orderBy('ingredient_name', 'asc')->get();

        if (Auth::check()) {
            $favorites = Favorite::query()->where('user_id', Auth::id())->orderBy('created_at', 'desc')->get();
        } else {
            $favorites = collect();
        }

        $historyQuery = ConversionHistory::query();
        $search = trim((string) $request->input('history_search', ''));
        $unit = trim((string) $request->input('history_unit', ''));
        $ingredient = trim((string) $request->input('history_ingredient', ''));
        $sort = $request->input('history_sort', 'desc') === 'asc' ? 'asc' : 'desc';

        if ($search !== '') {
            $historyQuery->where(function ($query) use ($search): void {
                $query->where('result_text', 'like', '%' . $search . '%')
                    ->orWhere('ingredient', 'like', '%' . $search . '%')
                    ->orWhere('from_unit', 'like', '%' . $search . '%')
                    ->orWhere('to_unit', 'like', '%' . $search . '%');
            });
        }

        if ($unit !== '') {
            $historyQuery->where(function ($query) use ($unit): void {
                $query->where('from_unit', $unit)
                    ->orWhere('to_unit', $unit);
            });
        }

        if ($ingredient !== '') {
            $historyQuery->where('ingredient', 'like', '%' . $ingredient . '%');
        }

        $history = $historyQuery
            ->orderBy('created_at', $sort)
            ->paginate(8)
            ->appends($request->only(['history_search', 'history_unit', 'history_ingredient', 'history_sort']));

        return view('welcome', compact(
            'units',
            'notes',
            'favorites',
            'history',
            'search',
            'unit',
            'ingredient',
            'sort'
        ));
    }
}
