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
            $table->boolean('is_waiver_pending')->default(false)->after('status');
            $table->foreignId('waived_by')->nullable()->after('is_waiver_pending')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fuel_orders', function (Blueprint $table) {
            $table->dropForeign(['waived_by']);
            $table->dropColumn(['is_waiver_pending', 'waived_by']);
        });
    }
};
