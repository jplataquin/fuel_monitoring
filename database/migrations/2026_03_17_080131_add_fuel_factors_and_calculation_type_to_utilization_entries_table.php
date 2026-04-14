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
            $table->decimal('kilometer_factor', 8, 2)->nullable()->after('kilometer_reading');
            $table->decimal('hour_factor', 8, 2)->nullable()->after('hour_reading');
            $table->string('calculation_type')->nullable()->after('fuel_order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('utilization_entries', function (Blueprint $table) {
            $table->dropColumn(['kilometer_factor', 'hour_factor', 'calculation_type']);
        });
    }
};
