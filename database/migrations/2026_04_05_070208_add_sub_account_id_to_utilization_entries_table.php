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
            $table->foreignId('sub_account_id')->nullable()->after('chargeable_account_id')->constrained('sub_accounts')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('utilization_entries', function (Blueprint $table) {
            $table->dropForeign(['sub_account_id']);
            $table->dropColumn('sub_account_id');
        });
    }
};
