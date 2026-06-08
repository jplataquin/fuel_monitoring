<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetType;
use App\Models\ChargeableAccount;
use App\Models\FuelOrder;
use App\Models\SubAccount;
use App\Models\User;
use App\Models\UtilizationEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChargeableAccountReportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Asset $asset;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'administrator']);

        $assetType = AssetType::create(['name' => 'Truck']);
        $this->asset = Asset::create([
            'fleet_no' => 'T-500',
            'asset_type_id' => $assetType->id,
            'fuel_factor_km' => 2.0,
            'fuel_factor_hr' => 1.0,
            'tank_capacity' => 150,
        ]);
    }

    public function test_running_account_requires_and_uses_provided_dates(): void
    {
        $account = ChargeableAccount::create([
            'name' => 'Running Project',
            'classification' => 'Running',
            'status' => 'Active',
        ]);

        $subAccount = SubAccount::create([
            'chargeable_account_id' => $account->id,
            'name' => 'Sub 1',
        ]);

        // Fuel order created in June
        $orderInJune = FuelOrder::create([
            'asset_id' => $this->asset->id,
            'status' => 'DONE',
            'actual_quantity' => 100,
        ]);
        $orderInJune->created_at = '2026-06-15 10:00:00';
        $orderInJune->save();

        UtilizationEntry::create([
            'asset_id' => $this->asset->id,
            'date' => '2026-06-15',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'chargeable_account_id' => $account->id,
            'sub_account_id' => $subAccount->id,
            'fuel_order_id' => $orderInJune->id,
            'calculation_type' => 'Actual Operation Hours',
            'driver_operator_name' => 'Operator',
            'reference' => 'REF-01',
            'particulars' => 'Job task',
        ]);

        // Fuel order created in July
        $orderInJuly = FuelOrder::create([
            'asset_id' => $this->asset->id,
            'status' => 'DONE',
            'actual_quantity' => 50,
        ]);
        $orderInJuly->created_at = '2026-07-15 10:00:00';
        $orderInJuly->save();

        UtilizationEntry::create([
            'asset_id' => $this->asset->id,
            'date' => '2026-07-15',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'chargeable_account_id' => $account->id,
            'sub_account_id' => $subAccount->id,
            'fuel_order_id' => $orderInJuly->id,
            'calculation_type' => 'Actual Operation Hours',
            'driver_operator_name' => 'Operator',
            'reference' => 'REF-02',
            'particulars' => 'Job task',
        ]);

        // Request report for June
        $response = $this->actingAs($this->admin)->get(route('reports.chargeable-accounts', [
            'account_id' => $account->id,
            'date_from' => '2026-06-01',
            'date_to' => '2026-06-30',
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('accountSummaries');

        $summaries = $response->viewData('accountSummaries');
        $this->assertCount(1, $summaries);

        // Prorated June quantity is 100
        $this->assertEquals(100.0, $summaries['Running Project']['actual_fuel']);
    }

    public function test_scoped_account_automatically_filters_by_scoped_dates(): void
    {
        // Account scoped to July
        $account = ChargeableAccount::create([
            'name' => 'July Scoped Project',
            'classification' => 'Scoped',
            'start_date' => '2026-07-01',
            'end_date' => '2026-07-31',
            'status' => 'Active',
        ]);

        $subAccount = SubAccount::create([
            'chargeable_account_id' => $account->id,
            'name' => 'Sub July',
        ]);

        // Fuel order created in June (should be ignored by report because account scope is July)
        $orderInJune = FuelOrder::create([
            'asset_id' => $this->asset->id,
            'status' => 'DONE',
            'actual_quantity' => 100,
        ]);
        $orderInJune->created_at = '2026-06-15 10:00:00';
        $orderInJune->save();

        UtilizationEntry::create([
            'asset_id' => $this->asset->id,
            'date' => '2026-06-15',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'chargeable_account_id' => $account->id,
            'sub_account_id' => $subAccount->id,
            'fuel_order_id' => $orderInJune->id,
            'calculation_type' => 'Actual Operation Hours',
            'driver_operator_name' => 'Operator',
            'reference' => 'REF-01',
            'particulars' => 'Job task',
        ]);

        // Fuel order created in July (should be included in report)
        $orderInJuly = FuelOrder::create([
            'asset_id' => $this->asset->id,
            'status' => 'DONE',
            'actual_quantity' => 80,
        ]);
        $orderInJuly->created_at = '2026-07-15 10:00:00';
        $orderInJuly->save();

        UtilizationEntry::create([
            'asset_id' => $this->asset->id,
            'date' => '2026-07-15',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'chargeable_account_id' => $account->id,
            'sub_account_id' => $subAccount->id,
            'fuel_order_id' => $orderInJuly->id,
            'calculation_type' => 'Actual Operation Hours',
            'driver_operator_name' => 'Operator',
            'reference' => 'REF-02',
            'particulars' => 'Job task',
        ]);

        // Request report without specifying any dates (simulate hidden JS fields being empty)
        $response = $this->actingAs($this->admin)->get(route('reports.chargeable-accounts', [
            'account_id' => $account->id,
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('accountSummaries');

        $summaries = $response->viewData('accountSummaries');
        $this->assertCount(1, $summaries);

        // It should automatically filter and only include July data (80 L), ignoring June data
        $this->assertEquals(80.0, $summaries['July Scoped Project']['actual_fuel']);
    }
}
