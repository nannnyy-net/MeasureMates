<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConversionHistory extends Model
{
    use HasFactory;

    protected $table = 'conversion_history';

    protected $fillable = [
        // Canonical columns used by the app
        'value_entered',
        'from_unit',
        'to_unit',
        'converted_value',
        'result_text',
        'ingredient',

        // Legacy/test-friendly aliases (some tests seed `amount`, `result`, `phrase`)
        'amount',
        'result',
        'phrase',
    ];


    protected $casts = [
        'value_entered' => 'float',
        'converted_value' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
