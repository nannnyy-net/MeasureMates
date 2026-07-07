<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ingredient_notes', function (Blueprint $table) {
            if (!Schema::hasColumn('ingredient_notes', 'is_favorite')) {
                $table->boolean('is_favorite')->default(false)->after('notes');
            }
        });

        if (Schema::hasTable('favorites')) {
            Schema::table('favorites', function (Blueprint $table) {
                if (!Schema::hasColumn('favorites', 'favorite_key')) {
                    $table->char('favorite_key', 64)->nullable()->after('ingredient');
                }
            });

            $favorites = DB::table('favorites')->get();
            foreach ($favorites as $favorite) {
                if (empty($favorite->favorite_key)) {
                    $favoriteKey = hash('sha256', sprintf('%s|%s|%s|%s|%s', (string) ($favorite->user_id ?? ''), mb_strtolower((string) $favorite->from_unit), mb_strtolower((string) $favorite->to_unit), number_format((float) $favorite->amount, 6, '.', ''), trim((string) ($favorite->ingredient ?? ''))));
                    DB::table('favorites')->where('id', $favorite->id)->update(['favorite_key' => $favoriteKey]);
                }
            }

            Schema::table('favorites', function (Blueprint $table) {
                try {
                    $table->dropUnique('fav_key_uq');
                } catch (\Throwable $e) {
                    // ignore missing indexes
                }

                try {
                    $table->unique(['user_id', 'favorite_key'], 'favorites_user_favorite_key_unique');
                } catch (\Throwable $e) {
                    // ignore duplicate indexes
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ingredient_notes', function (Blueprint $table) {
            if (Schema::hasColumn('ingredient_notes', 'is_favorite')) {
                $table->dropColumn('is_favorite');
            }
        });

        if (Schema::hasTable('favorites')) {
            Schema::table('favorites', function (Blueprint $table) {
                try {
                    $table->dropUnique('favorites_user_favorite_key_unique');
                } catch (\Throwable $e) {
                    // ignore
                }

                try {
                    $table->unique(['favorite_key'], 'fav_key_uq');
                } catch (\Throwable $e) {
                    // ignore
                }
            });
        }
    }
};
