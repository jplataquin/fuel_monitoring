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
        Schema::table('utilization_entries', function (Blueprint $table) {
            $table->dropUnique('asset_datetime_unique');
            $table->index(['asset_id', 'date', 'start_time'], 'asset_datetime_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('utilization_entries', function (Blueprint $table) {
            $table->dropIndex('asset_datetime_index');
            $table->unique(['asset_id', 'date', 'start_time'], 'asset_datetime_unique');
        });
    }
};
