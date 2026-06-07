<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First update the data
        DB::table('chargeable_accounts')
            ->where('classification', 'Periodic')
            ->update(['classification' => 'Scoped']);

        // Then update the column definition
        Schema::table('chargeable_accounts', function (Blueprint $table) {
            $table->enum('classification', ['Running', 'Scoped'])->default('Running')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('chargeable_accounts')
            ->where('classification', 'Scoped')
            ->update(['classification' => 'Periodic']);

        Schema::table('chargeable_accounts', function (Blueprint $table) {
            $table->enum('classification', ['Running', 'Periodic'])->default('Running')->change();
        });
    }
};
