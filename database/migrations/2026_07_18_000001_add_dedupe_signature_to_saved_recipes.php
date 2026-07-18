<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::table('saved_recipes', function (Blueprint $table) {
            $table->string('dedupe_signature', 64)->nullable();
        });

        // Backfill existing rows (best-effort) so we can safely add the NOT NULL + unique index.
        $rows = DB::table('saved_recipes')->whereNull('dedupe_signature')->get();

        foreach ($rows as $row) {
            $normalized = [
                'title' => trim((string) ($row->title ?? '')),
                'original_recipe' => trim(preg_replace('/\s+/', ' ', str_replace(["\r\n", "\r"], "\n", (string) ($row->original_recipe ?? '')))),
                'converted_recipe' => trim(preg_replace('/\s+/', ' ', str_replace(["\r\n", "\r"], "\n", (string) ($row->converted_recipe ?? '')))),
                'target_unit' => trim((string) ($row->target_unit ?? '')),
            ];

            $signature = hash('sha256', json_encode($normalized, JSON_UNESCAPED_UNICODE));

            DB::table('saved_recipes')
                ->where('id', $row->id)
                ->update(['dedupe_signature' => $signature]);
        }

        Schema::table('saved_recipes', function (Blueprint $table) {
            $table->string('dedupe_signature', 64)->nullable(false)->change();
            $table->unique('dedupe_signature', 'saved_recipes_dedupe_signature_unique');
        });
    }

    public function down(): void
    {
        Schema::table('saved_recipes', function (Blueprint $table) {
            $table->dropUnique('saved_recipes_dedupe_signature_unique');
            $table->dropColumn('dedupe_signature');
        });
    }
};

