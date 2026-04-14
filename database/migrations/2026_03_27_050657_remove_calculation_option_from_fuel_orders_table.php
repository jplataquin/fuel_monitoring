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
        Schema::table('fuel_orders', function (Blueprint $table) {
            $table->dropColumn('calculation_option');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fuel_orders', function (Blueprint $table) {
            $table->enum('calculation_option', ['kilometer_reading', 'hour_reading', 'actual_operation_time'])->nullable();
        });
    }
};
