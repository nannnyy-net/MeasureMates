<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('favorites', function (Blueprint $table) {
            // Add per-user favorites if missing.
            if (!Schema::hasColumn('favorites', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('id');
            }

            // Ingredient-aware uniqueness for future compatibility.
            // Note: we may already have this column from a partially-run migration.
            if (!Schema::hasColumn('favorites', 'ingredient')) {
                $table->string('ingredient', 191)->nullable()->after('to_unit');
            }

            // MySQL-specific drop of the old unique index.
            // sqlite (used in tests) does not support INFORMATION_SCHEMA.
            $connection = Schema::getConnection();
            $driver = method_exists($connection, 'getDriverName') ? $connection->getDriverName() : null;

            if ($driver === 'mysql') {
                $tableName = 'favorites';
                $oldIndex = 'favorites_from_unit_to_unit_amount_unique';

                $exists = DB::table('information_schema.STATISTICS')
                    ->selectRaw('COUNT(*) as aggregate')
                    ->where('TABLE_SCHEMA', DB::raw('database()'))
                    ->where('TABLE_NAME', $tableName)
                    ->where('INDEX_NAME', $oldIndex)
                    ->value('aggregate');

                if ((int) $exists > 0) {
                    DB::statement("ALTER TABLE {$tableName} DROP INDEX {$oldIndex}");
                }
            }

            // Enforce uniqueness using a single hashed key to avoid MySQL composite
            // index length issues with DOUBLE and large string columns.
            // Avoid duplicate attempts for partially-run migrations.
            if (!Schema::hasColumn('favorites', 'favorite_key')) {
                $table->char('favorite_key', 64)->nullable()->after('ingredient');
            }

            // Create unique constraint on favorite_key.
            // On sqlite/test runs, duplicate unique creation can happen if this migration
            // is invoked more than once in the same in-memory schema.
            try {
                $table->unique(['favorite_key'], 'fav_key_uq');
            } catch (\Throwable $e) {
                // ignore duplicate unique creation
            }
        });

        // Add foreign key constraint.
        // Attempt once; if it already exists in partially-run situations, ignore.
        try {
            Schema::table('favorites', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            });
        } catch (\Throwable $e) {
            // ignore
        }
    }

    public function down(): void
    {
        Schema::table('favorites', function (Blueprint $table) {
            // Drop the new unique constraint.
            try {
                $table->dropUnique('fav_key_uq');
            } catch (\Throwable $e) {
                // ignore
            }

            // Drop columns.
            if (Schema::hasColumn('favorites', 'favorite_key')) {
                $table->dropColumn('favorite_key');
            }
            if (Schema::hasColumn('favorites', 'ingredient')) {
                $table->dropColumn('ingredient');
            }

            if (Schema::hasColumn('favorites', 'user_id')) {
                try {
                    $table->dropForeign(['user_id']);
                } catch (\Throwable $e) {
                    // ignore
                }
                $table->dropColumn('user_id');
            }

            // Restore original unique index (best-effort).
            try {
                $table->unique(['from_unit', 'to_unit', 'amount']);
            } catch (\Throwable $e) {
                // ignore
            }
        });
    }
};

