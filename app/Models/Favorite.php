<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'from_unit',
        'to_unit',
        'amount',
        'ingredient',
        'favorite_key',
    ];

    protected $casts = [
        'amount' => 'float',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $favorite): void {
            $favorite->favorite_key = $favorite->resolveFavoriteKey(
                $favorite->user_id,
                $favorite->from_unit,
                $favorite->to_unit,
                $favorite->amount,
                $favorite->ingredient,
            );
        });

        static::updating(function (self $favorite): void {
            if ($favorite->isDirty(['user_id', 'from_unit', 'to_unit', 'amount', 'ingredient'])) {
                $favorite->favorite_key = $favorite->resolveFavoriteKey(
                    $favorite->user_id,
                    $favorite->from_unit,
                    $favorite->to_unit,
                    $favorite->amount,
                    $favorite->ingredient,
                );
            }
        });
    }

    public static function buildFavoriteKey(?int $userId, string $fromUnit, string $toUnit, float $amount, ?string $ingredient): string
    {
        $normalizedAmount = number_format((float) $amount, 6, '.', '');
        $normalizedFromUnit = mb_strtolower(trim((string) $fromUnit));
        $normalizedToUnit = mb_strtolower(trim((string) $toUnit));
        $normalizedIngredient = trim((string) ($ingredient ?? '')) === '' ? '' : mb_strtolower(trim((string) $ingredient));

        return hash('sha256', sprintf('%s|%s|%s|%s|%s', $userId ?? '', $normalizedFromUnit, $normalizedToUnit, $normalizedAmount, $normalizedIngredient));
    }

    public function resolveFavoriteKey(?int $userId = null, ?string $fromUnit = null, ?string $toUnit = null, ?float $amount = null, ?string $ingredient = null): string
    {
        return self::buildFavoriteKey(
            $userId ?? $this->user_id,
            $fromUnit ?? $this->from_unit,
            $toUnit ?? $this->to_unit,
            $amount ?? (float) $this->amount,
            $ingredient ?? $this->ingredient,
        );
    }
}
