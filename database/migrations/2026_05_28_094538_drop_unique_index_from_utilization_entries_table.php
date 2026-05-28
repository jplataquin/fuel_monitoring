<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, create the new index so that the foreign key constraint
        // always has an index to refer to.
        Schema::table('utilization_entries', function (Blueprint $table) {
            $table->index(['asset_id', 'date', 'start_time'], 'asset_datetime_index');
        });

        // Now we can safely drop the unique index.
        Schema::table('utilization_entries', function (Blueprint $table) {
            $table->dropUnique('asset_datetime_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-create the unique index first.
        Schema::table('utilization_entries', function (Blueprint $table) {
            $table->unique(['asset_id', 'date', 'start_time'], 'asset_datetime_unique');
        });

        // Then drop the non-unique index.
        Schema::table('utilization_entries', function (Blueprint $table) {
            $table->dropIndex('asset_datetime_index');
        });
    }
};
