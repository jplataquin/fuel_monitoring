<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('utilization_entries')
            ->where('calculation_type', 'Actual Operation Hours')
            ->update(['calculation_type' => 'Timeframe']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('utilization_entries')
            ->where('calculation_type', 'Timeframe')
            ->update(['calculation_type' => 'Actual Operation Hours']);
    }
};
