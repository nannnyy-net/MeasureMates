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
        if (Schema::hasTable('conversion_history')) {
            return;
        }

        if (Schema::hasTable('conversion_histories')) {
            Schema::rename('conversion_histories', 'conversion_history');
        } else {
            Schema::create('conversion_history', function (Blueprint $table) {
                $table->id();
                $table->decimal('value_entered', 12, 6);
                $table->string('from_unit');
                $table->string('to_unit');
                $table->decimal('converted_value', 12, 6);
                $table->text('result_text');
                $table->string('ingredient')->nullable();
                $table->timestamps();

                $table->index(['from_unit', 'to_unit']);
                $table->index('ingredient');
                $table->index('created_at');
                $table->unique(['from_unit', 'to_unit', 'value_entered', 'ingredient'], 'conv_hist_unique');
            });

            return;
        }

        Schema::table('conversion_history', function (Blueprint $table) {
            if (!Schema::hasColumn('conversion_history', 'value_entered')) {
                $table->decimal('value_entered', 12, 6)->nullable()->after('id');
            }

            if (!Schema::hasColumn('conversion_history', 'converted_value')) {
                $table->decimal('converted_value', 12, 6)->nullable()->after('to_unit');
            }

            if (!Schema::hasColumn('conversion_history', 'result_text')) {
                $table->text('result_text')->nullable()->after('converted_value');
            }

            if (!Schema::hasColumn('conversion_history', 'ingredient')) {
                $table->string('ingredient')->nullable()->after('result_text');
            }

            if (!Schema::hasColumn('conversion_history', 'created_at')) {
                $table->timestamps();
            }
        });

        if (Schema::hasColumn('conversion_history', 'amount')) {
            DB::table('conversion_history')->update([
                'value_entered' => DB::raw('amount'),
                'converted_value' => DB::raw('result'),
                'result_text' => DB::raw('phrase'),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('conversion_history')) {
            Schema::dropIfExists('conversion_history');
        }

        if (Schema::hasTable('conversion_histories')) {
            Schema::dropIfExists('conversion_histories');
        }
    }
};
