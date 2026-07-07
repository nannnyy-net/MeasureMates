<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_unit',
        'to_unit',
        'amount',
    ];

    protected $casts = [
        'amount' => 'float',
    ];
}
