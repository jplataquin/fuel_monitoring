<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_assets_index_page_groups_assets_in_tabs_by_classification_with_counts()
    {
        $user = User::factory()->create(['role' => 'administrator']);
        
        $typeBackhoe = AssetType::create(['name' => 'Backhoe']);
        $typeTruck = AssetType::create(['name' => 'Truck']);

        // Create 3 Backhoes
        Asset::create([
            'fleet_no' => 'BH-01',
            'asset_type_id' => $typeBackhoe->id,
            'tank_capacity' => 100,
            'last_kilometer_reading' => 0,
            'last_engine_hours' => 0,
            'fuel_type' => 'Diesel',
        ]);
        Asset::create([
            'fleet_no' => 'BH-02',
            'asset_type_id' => $typeBackhoe->id,
            'tank_capacity' => 100,
            'last_kilometer_reading' => 0,
            'last_engine_hours' => 0,
            'fuel_type' => 'Diesel',
        ]);
        Asset::create([
            'fleet_no' => 'BH-03',
            'asset_type_id' => $typeBackhoe->id,
            'tank_capacity' => 100,
            'last_kilometer_reading' => 0,
            'last_engine_hours' => 0,
            'fuel_type' => 'Diesel',
        ]);

        // Create 1 Truck
        Asset::create([
            'fleet_no' => 'TR-01',
            'asset_type_id' => $typeTruck->id,
            'tank_capacity' => 150,
            'last_kilometer_reading' => 0,
            'last_engine_hours' => 0,
            'fuel_type' => 'Diesel',
        ]);

        $response = $this->actingAs($user)->get(route('assets.index'));

        $response->assertStatus(200);
        
        // Check that classifications and counts are rendered correctly including the "All" tab
        $response->assertSee('All (4)');
        $response->assertSee('Backhoe (3)');
        $response->assertSee('Truck (1)');

        // Check that Alpine.js state is initialized to 'all' as the default active tab
        $response->assertSee("activeTab: 'all'", false);

        // Check that searching automatically sets the active tab to 'all'
        $response->assertSee("activeTab = 'all'", false);
    }
}
