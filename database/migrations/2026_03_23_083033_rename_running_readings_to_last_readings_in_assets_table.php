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
        Schema::table('assets', function (Blueprint $table) {
            $table->renameColumn('running_kilometer_reading', 'last_kilometer_reading');
            $table->renameColumn('running_engine_hours', 'last_engine_hours');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->renameColumn('last_kilometer_reading', 'running_kilometer_reading');
            $table->renameColumn('last_engine_hours', 'running_engine_hours');
        });
    }
};
