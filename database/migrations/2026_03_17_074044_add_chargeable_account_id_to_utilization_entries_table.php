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
            $table->foreignId('chargeable_account_id')->nullable()->after('driver_operator_name')->constrained('chargeable_accounts')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('utilization_entries', function (Blueprint $table) {
            $table->dropForeign(['chargeable_account_id']);
            $table->dropColumn('chargeable_account_id');
        });
    }
};
