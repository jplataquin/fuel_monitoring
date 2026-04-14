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
        // Rename vehicle_types to asset_types
        if (Schema::hasTable('vehicle_types') && ! Schema::hasTable('asset_types')) {
            Schema::rename('vehicle_types', 'asset_types');
        }

        // Rename vehicles to assets
        if (Schema::hasTable('vehicles') && ! Schema::hasTable('assets')) {
            Schema::rename('vehicles', 'assets');

            Schema::table('assets', function (Blueprint $table) {
                if (Schema::hasColumn('assets', 'vehicle_type_id')) {
                    $table->renameColumn('vehicle_type_id', 'asset_type_id');
                }
            });
        }

        // Update utilization_entries table
        if (Schema::hasTable('utilization_entries')) {
            Schema::table('utilization_entries', function (Blueprint $table) {
                if (Schema::hasColumn('utilization_entries', 'vehicle_id')) {
                    $table->renameColumn('vehicle_id', 'asset_id');
                }
            });

            Schema::table('utilization_entries', function (Blueprint $table) {
                // Add a temporary index to satisfy the foreign key constraint while we swap the unique index
                $table->index('asset_id', 'temp_asset_id_index');

                // Update the unique constraint name
                $indexes = Schema::getIndexes('utilization_entries');
                $indexNames = array_column($indexes, 'name');

                if (in_array('vehicle_datetime_unique', $indexNames)) {
                    $table->dropUnique('vehicle_datetime_unique');
                }

                if (! in_array('asset_datetime_unique', $indexNames)) {
                    $table->unique(['asset_id', 'date', 'time'], 'asset_datetime_unique');
                }

                // Drop the temporary index
                $table->dropIndex('temp_asset_id_index');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert utilization_entries table
        if (Schema::hasTable('utilization_entries')) {
            Schema::table('utilization_entries', function (Blueprint $table) {
                if (Schema::hasColumn('utilization_entries', 'asset_id')) {
                    $table->renameColumn('asset_id', 'vehicle_id');
                }

                $table->dropUnique('asset_datetime_unique');
                $table->unique(['vehicle_id', 'date', 'time'], 'vehicle_datetime_unique');
            });
        }

        // Revert assets to vehicles
        if (Schema::hasTable('assets') && ! Schema::hasTable('vehicles')) {
            Schema::table('assets', function (Blueprint $table) {
                if (Schema::hasColumn('assets', 'asset_type_id')) {
                    $table->renameColumn('asset_type_id', 'vehicle_type_id');
                }
            });

            Schema::rename('assets', 'vehicles');
        }

        // Revert asset_types to vehicle_types
        if (Schema::hasTable('asset_types') && ! Schema::hasTable('vehicle_types')) {
            Schema::rename('asset_types', 'vehicle_types');
        }
    }
};
