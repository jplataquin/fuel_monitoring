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
            $table->renameColumn('kilometer_factor', 'fuel_factor_km');
            $table->renameColumn('hour_factor', 'fuel_factor_hr');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('utilization_entries', function (Blueprint $table) {
            $table->renameColumn('fuel_factor_km', 'kilometer_factor');
            $table->renameColumn('fuel_factor_hr', 'hour_factor');
        });
    }
};
