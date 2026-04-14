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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('fleet_no')->unique();
            $table->foreignId('asset_type_id')->constrained('asset_types')->onDelete('cascade');
            $table->float('fuel_factor_km')->nullable();
            $table->float('fuel_factor_hr')->nullable();
            $table->string('plate_no')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
