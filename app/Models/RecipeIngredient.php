<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecipeIngredient extends Model
{
    use HasFactory;

    protected $fillable = [
        'recipe_id',
        'ingredient_name',
        'quantity',
        'original_unit',
        'converted_quantity',
        'converted_unit',
        'display_order',
    ];

    protected $casts = [
        'quantity' => 'double',
        'converted_quantity' => 'double',
        'display_order' => 'integer',
    ];

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }
}
