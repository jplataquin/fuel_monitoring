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
            $table->float('last_kilometer_reading')->nullable();
            $table->float('last_engine_hours')->nullable();
            $table->time('last_time')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('utilization_entries', function (Blueprint $table) {
            $table->dropColumn(['last_kilometer_reading', 'last_engine_hours', 'last_time']);
        });
    }
};
