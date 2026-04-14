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
            $table->foreignId('actualized_by')->nullable()->constrained('users');
            $table->timestamp('actualized_at')->nullable();
            $table->foreignId('void_by')->nullable()->constrained('users');
            $table->timestamp('void_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fuel_orders', function (Blueprint $table) {
            $table->dropForeign(['actualized_by']);
            $table->dropForeign(['void_by']);
            $table->dropColumn(['actualized_by', 'actualized_at', 'void_by', 'void_at']);
        });
    }
};
