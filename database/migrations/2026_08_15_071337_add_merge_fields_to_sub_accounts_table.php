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
            $table->foreignId('merged_to_id')->nullable()->after('name')->constrained('sub_accounts')->onDelete('set null');
            $table->foreignId('merged_by')->nullable()->after('merged_to_id')->constrained('users')->onDelete('set null');
            $table->timestamp('merged_at')->nullable()->after('merged_by');
            $table->text('merge_remarks')->nullable()->after('merged_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sub_accounts', function (Blueprint $table) {
            $table->dropForeign(['merged_to_id']);
            $table->dropForeign(['merged_by']);
            $table->dropColumn(['merged_to_id', 'merged_by', 'merged_at', 'merge_remarks']);
        });
    }
};
