<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetType;
use App\Models\ChargeableAccount;
use App\Models\FuelOrder;
use App\Models\User;
use App\Models\UtilizationEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetUtilizationReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_asset_utilization_report_shows_slippage_variance_for_km()
    {
        $user = User::factory()->create(['role' => 'moderator']);
        $type = AssetType::create(['name' => 'Vehicle']);
        $account = ChargeableAccount::create(['name' => 'Project Alpha', 'status' => 'Active']);
        $asset = Asset::create([
            'fleet_no' => 'V-001',
            'asset_type_id' => $type->id,
            'fuel_factor_km' => 2.0, // 2.0 KM per liter
            'fuel_factor_hr' => 0,
            'tank_capacity' => 100,
        ]);

        // Create fuel order that is DONE
        $fuelOrder = FuelOrder::create([
            'asset_id' => $asset->id,
            'calculated_kilometers' => 200,
            'fuel_factor_km' => 2.0, // 2 KM per liter
            'actual_quantity' => 110, // Actual consumption is 110L for 200KM -> 200/110 = 0.55 L/KM. Target L/KM = 1/2 = 0.5. Slippage = (0.55/0.5)-1 = +10%
            'status' => 'DONE',
            'date_from' => '2026-04-01',
            'date_to' => '2026-04-02',
        ]);

        // Create utilization entries linked to this fuel order
        UtilizationEntry::create([
            'asset_id' => $asset->id,
            'fuel_order_id' => $fuelOrder->id,
            'date' => '2026-04-01',
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
            'reference' => 'REF-001',
            'particulars' => 'Work',
            'driver_operator_name' => 'John Doe',
            'start_kilometer_reading' => 1000,
            'end_kilometer_reading' => 1200,
            'calculation_type' => 'kilometer reading',
            'chargeable_account_id' => $account->id,
            'kilometer_factor' => 2.0,
        ]);

        $response = $this->actingAs($user)->get(route('reports.asset-utilization', [
            'asset_id' => $asset->id,
            'date_from' => '2026-04-01',
            'date_to' => '2026-04-02',
        ]));

        $response->assertStatus(200);
        $response->assertSee('+10.00%');
    }

    public function test_asset_utilization_report_shows_slippage_variance_for_hr()
    {
        $user = User::factory()->create(['role' => 'moderator']);
        $type = AssetType::create(['name' => 'Generator']);
        $account = ChargeableAccount::create(['name' => 'Project Alpha', 'status' => 'Active']);
        $asset = Asset::create([
            'fleet_no' => 'G-001',
            'asset_type_id' => $type->id,
            'fuel_factor_km' => 0,
            'fuel_factor_hr' => 5.0, // 5 liters per hour
            'tank_capacity' => 100,
        ]);

        // Create fuel order that is DONE
        $fuelOrder = FuelOrder::create([
            'asset_id' => $asset->id,
            'calculated_hours' => 10,
            'fuel_factor_hr' => 5.0,
            'actual_quantity' => 60, // 60L / 10H = 6 L/H. Slippage = (6/5)-1 = +20%
            'status' => 'DONE',
            'date_from' => '2026-04-01',
            'date_to' => '2026-04-02',
        ]);

        // Create utilization entries linked to this fuel order
        UtilizationEntry::create([
            'asset_id' => $asset->id,
            'fuel_order_id' => $fuelOrder->id,
            'date' => '2026-04-01',
            'start_time' => '08:00:00',
            'end_time' => '18:00:00',
            'reference' => 'REF-002',
            'particulars' => 'Work',
            'driver_operator_name' => 'John Doe',
            'start_hour_reading' => 100,
            'end_hour_reading' => 110,
            'calculation_type' => 'hour reading',
            'chargeable_account_id' => $account->id,
            'hour_factor' => 5.0,
        ]);

        $response = $this->actingAs($user)->get(route('reports.asset-utilization', [
            'asset_id' => $asset->id,
            'date_from' => '2026-04-01',
            'date_to' => '2026-04-02',
        ]));

        $response->assertStatus(200);
        $response->assertSee('+20.00%');
    }
}
