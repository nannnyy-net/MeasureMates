<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IngredientNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'ingredient_name',
        'notes',
        'is_favorite',
    ];

    protected $casts = [
        'is_favorite' => 'boolean',
    ];
}
