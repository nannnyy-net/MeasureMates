<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavedRecipe extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'original_recipe',
        'converted_recipe',
        'target_unit',
        'dedupe_signature',
    ];


    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public $timestamps = true;
}
