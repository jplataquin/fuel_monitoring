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
            $table->renameColumn('kilometer_reading', 'start_kilometer_reading');
            $table->renameColumn('hour_reading', 'start_hour_reading');
        });

        Schema::table('utilization_entries', function (Blueprint $table) {
            $table->float('end_kilometer_reading')->nullable()->default(0)->after('start_kilometer_reading');
            $table->float('end_hour_reading')->nullable()->default(0)->after('start_hour_reading');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('utilization_entries', function (Blueprint $table) {
            $table->dropColumn('end_kilometer_reading');
            $table->dropColumn('end_hour_reading');
        });

        Schema::table('utilization_entries', function (Blueprint $table) {
            $table->renameColumn('start_kilometer_reading', 'kilometer_reading');
            $table->renameColumn('start_hour_reading', 'hour_reading');
        });
    }
};
