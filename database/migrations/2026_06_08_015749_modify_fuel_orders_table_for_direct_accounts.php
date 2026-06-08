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
        // 1. Drop the foreign key constraint first
        Schema::table('fuel_orders', function (Blueprint $table) {
            $table->dropForeign(['asset_id']);
        });

        // 2. Modify the column to be nullable and add new columns
        Schema::table('fuel_orders', function (Blueprint $table) {
            $table->foreignId('asset_id')->nullable()->change();
            $table->foreign('asset_id')->references('id')->on('assets')->onDelete('cascade');

            $table->foreignId('chargeable_account_id')->nullable()->constrained('chargeable_accounts')->onDelete('set null');
            $table->foreignId('sub_account_id')->nullable()->constrained('sub_accounts')->onDelete('set null');
            $table->boolean('unbudgeted')->default(false);
            $table->text('remarks')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fuel_orders', function (Blueprint $table) {
            $table->dropForeign(['chargeable_account_id']);
            $table->dropForeign(['sub_account_id']);
            $table->dropColumn(['chargeable_account_id', 'sub_account_id', 'unbudgeted', 'remarks']);

            $table->dropForeign(['asset_id']);
        });

        Schema::table('fuel_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('asset_id')->nullable(false)->change();
            $table->foreign('asset_id')->references('id')->on('assets')->onDelete('cascade');
        });
    }
};
