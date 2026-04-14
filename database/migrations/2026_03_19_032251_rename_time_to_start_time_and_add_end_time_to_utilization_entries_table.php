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
            $table->index('asset_id', 'temp_asset_id_index');
            $table->dropUnique('asset_datetime_unique');
            $table->renameColumn('time', 'start_time');
        });

        Schema::table('utilization_entries', function (Blueprint $table) {
            $table->time('end_time')->nullable()->after('start_time');
            $table->unique(['asset_id', 'date', 'start_time'], 'asset_datetime_unique');
            $table->dropIndex('temp_asset_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('utilization_entries', function (Blueprint $table) {
            $table->index('asset_id', 'temp_asset_id_index');
            $table->dropUnique('asset_datetime_unique');
            $table->dropColumn('end_time');
        });

        Schema::table('utilization_entries', function (Blueprint $table) {
            $table->renameColumn('start_time', 'time');
        });

        Schema::table('utilization_entries', function (Blueprint $table) {
            $table->unique(['asset_id', 'date', 'time'], 'asset_datetime_unique');
            $table->dropIndex('temp_asset_id_index');
        });
    }
};
