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
        Schema::table('chargeable_accounts', function (Blueprint $table) {
            $table->enum('classification', ['Running', 'Periodic'])->default('Running')->after('name');
            $table->date('start_date')->nullable()->after('classification');
            $table->date('end_date')->nullable()->after('start_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chargeable_accounts', function (Blueprint $table) {
            $table->dropColumn(['classification', 'start_date', 'end_date']);
        });
    }
};
