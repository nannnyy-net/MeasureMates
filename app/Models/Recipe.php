<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Recipe extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'category',
        'servings',
        'notes',
        'is_saved',
    ];

    protected $casts = [
        'servings' => 'integer',
        'is_saved' => 'boolean',
    ];

    public function ingredients(): HasMany
    {
        return $this->hasMany(RecipeIngredient::class)->orderBy('display_order');
    }

    public function conversions(): HasMany
    {
        return $this->hasMany(RecipeConversion::class);
    }
}
