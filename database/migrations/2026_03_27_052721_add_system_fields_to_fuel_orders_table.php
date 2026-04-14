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
            $table->double('calculated_hours')->default(0);
            $table->double('calculated_kilometers')->default(0);
            $table->double('fuel_factor_km')->default(0);
            $table->double('fuel_factor_hr')->default(0);
            $table->date('date_from')->nullable();
            $table->date('date_to')->nullable();
            $table->enum('status', ['PEND', 'DONE', 'VOID'])->default('PEND');
            $table->double('actual_quantity')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fuel_orders', function (Blueprint $table) {
            $table->dropColumn([
                'calculated_hours',
                'calculated_kilometers',
                'fuel_factor_km',
                'fuel_factor_hr',
                'date_from',
                'date_to',
                'status',
                'actual_quantity'
            ]);
        });
    }
};
