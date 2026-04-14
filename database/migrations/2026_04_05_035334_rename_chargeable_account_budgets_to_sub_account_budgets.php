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
        // First drop the old foreign key constraint
        Schema::table('chargeable_account_budgets', function (Blueprint $table) {
            $table->dropForeign(['chargeable_account_id']);
        });

        // Rename the table
        Schema::rename('chargeable_account_budgets', 'sub_account_budgets');

        Schema::table('sub_account_budgets', function (Blueprint $table) {
            // Rename the column
            $table->renameColumn('chargeable_account_id', 'sub_account_id');
        });
        
        // Add the new foreign key constraint
        Schema::table('sub_account_budgets', function (Blueprint $table) {
             $table->foreign('sub_account_id')->references('id')->on('sub_accounts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sub_account_budgets', function (Blueprint $table) {
            $table->dropForeign(['sub_account_id']);
            $table->renameColumn('sub_account_id', 'chargeable_account_id');
        });

        Schema::rename('sub_account_budgets', 'chargeable_account_budgets');

        Schema::table('chargeable_account_budgets', function (Blueprint $table) {
            $table->foreign('chargeable_account_id')->references('id')->on('chargeable_accounts')->onDelete('cascade');
        });
    }
};
