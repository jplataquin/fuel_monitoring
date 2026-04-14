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
        Schema::table('sub_accounts', function (Blueprint $table) {
            // First add a regular index so the foreign key doesn't block dropping the unique index
            $table->index(['chargeable_account_id'], 'sub_accounts_chargeable_account_id_index');
            
            // Now drop the old unique index
            $table->dropUnique(['chargeable_account_id', 'name']);
            
            // Add the new unique index including deleted_at
            $table->unique(['chargeable_account_id', 'name', 'deleted_at'], 'sub_accounts_chargeable_account_id_name_deleted_at_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sub_accounts', function (Blueprint $table) {
            $table->dropUnique(['chargeable_account_id', 'name', 'deleted_at']);
            $table->unique(['chargeable_account_id', 'name'], 'sub_accounts_chargeable_account_id_name_unique');
            $table->dropIndex('sub_accounts_chargeable_account_id_index');
        });
    }
};
