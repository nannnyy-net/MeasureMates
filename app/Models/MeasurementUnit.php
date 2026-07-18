<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeasurementUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'symbol',
        'type',
        'conversion_factor',
    ];

    protected $casts = [
        'conversion_factor' => 'double',
    ];
}
