<?php

namespace Tests\Feature;

use App\Livewire\CreateFuelOrder;
use App\Models\Asset;
use App\Models\AssetType;
use App\Models\ChargeableAccount;
use App\Models\FuelOrder;
use App\Models\User;
use App\Models\UtilizationEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FuelOrderFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_data_logger_can_access_fuel_order_routes()
    {
        $user = User::factory()->create(['role' => 'data_logger']);

        $response = $this->actingAs($user)->get(route('fuel-orders.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get(route('fuel-orders.create'));
        $response->assertStatus(200);
    }

    public function test_standard_user_cannot_access_create_fuel_order_route()
    {
        $user = User::factory()->create(['role' => 'moderator']);

        $response = $this->actingAs($user)->get(route('fuel-orders.create'));
        $response->assertStatus(403);
    }

    public function test_livewire_component_calculates_correctly_for_kilometer_reading()
    {
        $user = User::factory()->create(['role' => 'data_logger']);
        $type = AssetType::create(['name' => 'Vehicle']);
        $account = ChargeableAccount::create(['name' => 'Project Alpha', 'status' => 'Active']);
        $asset = Asset::create([
            'fleet_no' => 'V-001',
            'asset_type_id' => $type->id,
            'fuel_factor_km' => 2.5,
            'fuel_factor_hr' => 1.5,
            'tank_capacity' => 100,
        ]);

        UtilizationEntry::create([
            'asset_id' => $asset->id,
            'date' => '2026-03-01',
            'start_time' => '08:00',
            'end_time' => '17:00',
            'reference' => 'REF-001',
            'particulars' => 'Start',
            'start_kilometer_reading' => 1000,
            'end_kilometer_reading' => 1100, // 100 km diff
            'start_hour_reading' => 100,
            'end_hour_reading' => 100,
            'driver_operator_name' => 'John Doe',
            'chargeable_account_id' => $account->id,
            'calculation_type' => 'Kilometer Reading',
        ]);

        UtilizationEntry::create([
            'asset_id' => $asset->id,
            'date' => '2026-03-02',
            'start_time' => '09:00', // Different start time
            'end_time' => '17:00',
            'reference' => 'REF-002',
            'particulars' => 'End',
            'start_kilometer_reading' => 1200,
            'end_kilometer_reading' => 1300, // 100 km diff
            'start_hour_reading' => 110,
            'end_hour_reading' => 110,
            'driver_operator_name' => 'John Doe',
            'chargeable_account_id' => $account->id,
            'calculation_type' => 'Kilometer Reading',
        ]);

        Livewire::actingAs($user)
            ->test(CreateFuelOrder::class)
            ->set('asset_id', $asset->id)
            ->set('date_from', '2026-03-01')
            ->set('date_to', '2026-03-02')
            ->assertSet('unprocessed_entries_count', 2)
            ->assertSet('calculated_quantity', 80) // 200 / 2.5 = 80
            ->set('say_quantity', 80)
            ->call('submit')
            ->assertRedirect(route('fuel-orders.index'));

        $this->assertDatabaseHas('fuel_orders', [
            'asset_id' => $asset->id,
            'calculated_quantity' => 80,
            'say_quantity' => 80,
            'calculated_kilometers' => 200,
            'calculated_hours' => 0,
            'fuel_factor_km' => 2.5,
            'fuel_factor_hr' => 1.5,
            'date_from' => '2026-03-01',
            'date_to' => '2026-03-02',
            'status' => 'PEND',
            'actual_quantity' => 0,
        ]);

        $this->assertEquals(2, UtilizationEntry::whereNotNull('fuel_order_id')->count());
        $this->assertDatabaseHas('utilization_entries', [
            'asset_id' => $asset->id,
            'fuel_factor_km' => 2.5,
            'fuel_factor_hr' => 1.5,
            'calculation_type' => 'Kilometer Reading',
        ]);
    }

    public function test_livewire_component_groups_totals_by_chargeable_account()
    {
        $user = User::factory()->create(['role' => 'data_logger']);
        $type = AssetType::create(['name' => 'Vehicle']);
        $account1 = ChargeableAccount::create(['name' => 'Account A', 'status' => 'Active']);
        $account2 = ChargeableAccount::create(['name' => 'Account B', 'status' => 'Active']);
        $asset = Asset::create([
            'fleet_no' => 'V-001',
            'asset_type_id' => $type->id,
            'fuel_factor_km' => 2,
            'tank_capacity' => 100,
        ]);

        UtilizationEntry::create([
            'asset_id' => $asset->id,
            'date' => '2026-03-01',
            'start_time' => '08:00',
            'end_time' => '12:00',
            'reference' => 'REF-A1',
            'particulars' => 'Work A',
            'start_kilometer_reading' => 1000,
            'end_kilometer_reading' => 1050,
            'driver_operator_name' => 'John Doe',
            'chargeable_account_id' => $account1->id,
            'calculation_type' => 'Kilometer Reading',
        ]);

        UtilizationEntry::create([
            'asset_id' => $asset->id,
            'date' => '2026-03-01',
            'reference' => 'REF-001',
            'start_time' => '13:00',
            'end_time' => '17:00',
            'reference' => 'REF-B1',
            'particulars' => 'Work B',
            'start_kilometer_reading' => 1050,
            'end_kilometer_reading' => 1080,
            'driver_operator_name' => 'John Doe',
            'chargeable_account_id' => $account2->id,
            'calculation_type' => 'Kilometer Reading',
        ]);

        UtilizationEntry::create([
            'asset_id' => $asset->id,
            'date' => '2026-03-02',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'reference' => 'REF-A2',
            'particulars' => 'Work A2',
            'start_kilometer_reading' => 1080,
            'end_kilometer_reading' => 1100,
            'driver_operator_name' => 'John Doe',
            'chargeable_account_id' => $account1->id,
            'calculation_type' => 'Kilometer Reading',
        ]);

        Livewire::actingAs($user)
            ->test(CreateFuelOrder::class)
            ->set('asset_id', $asset->id)
            ->set('date_from', '2026-03-01')
            ->set('date_to', '2026-03-02')
            ->assertSet('unprocessed_entries_count', 3)
            ->assertSet('calculated_kilometers', 100)
            ->assertSet('calculated_quantity', 50)
            ->assertSet('grouped_totals', [
                'Account A' => [
                    'kilometers' => 70, // (1050-1000) + (1100-1080) = 50 + 20
                    'hours' => 0,
                    'quantity' => 35, // 70 / 2 = 35
                ],
                'Account B' => [
                    'kilometers' => 30, // (1080-1050) = 30
                    'hours' => 0,
                    'quantity' => 15, // 30 / 2 = 15
                ],
            ]);
    }

    public function test_livewire_component_calculates_correctly_for_actual_operation_time()
    {
        $user = User::factory()->create(['role' => 'data_logger']);
        $type = AssetType::create(['name' => 'Generator']);
        $account = ChargeableAccount::create(['name' => 'Maintenance Dept', 'status' => 'Active']);
        $asset = Asset::create([
            'fleet_no' => 'G-001',
            'asset_type_id' => $type->id,
            'fuel_factor_km' => 0,
            'fuel_factor_hr' => 5, // 5 liters per hour
            'tank_capacity' => 100,
        ]);

        UtilizationEntry::create([
            'asset_id' => $asset->id,
            'date' => '2026-03-01',
            'start_time' => '08:00',
            'end_time' => '08:00', // 0 hours difference
            'reference' => 'REF-001',
            'particulars' => 'Start',
            'start_kilometer_reading' => 0,
            'end_kilometer_reading' => 0,
            'start_hour_reading' => 100,
            'end_hour_reading' => 100,
            'driver_operator_name' => 'Jane Doe',
            'chargeable_account_id' => $account->id,
            'calculation_type' => 'Actual Operation Hours',
        ]);

        UtilizationEntry::create([
            'asset_id' => $asset->id,
            'date' => '2026-03-01',
            'reference' => 'REF-001',
            'start_time' => '11:00', // Changed from 08:00
            'end_time' => '13:30', // 2.5 hours difference
            'reference' => 'REF-003',
            'particulars' => 'End',
            'start_kilometer_reading' => 0,
            'end_kilometer_reading' => 0,
            'start_hour_reading' => 102.5,
            'end_hour_reading' => 102.5,
            'driver_operator_name' => 'Jane Doe',
            'chargeable_account_id' => $account->id,
            'calculation_type' => 'Actual Operation Hours',
        ]);

        Livewire::actingAs($user)
            ->test(CreateFuelOrder::class)
            ->set('asset_id', $asset->id)
            ->set('date_from', '2026-03-01')
            ->set('date_to', '2026-03-01')
            ->assertSet('unprocessed_entries_count', 2)
            ->assertSet('calculated_quantity', 12.5) // 2.5 hours * 5
            ->set('say_quantity', 13)
            ->call('submit')
            ->assertRedirect(route('fuel-orders.index'));

        $this->assertDatabaseHas('fuel_orders', [
            'asset_id' => $asset->id,
            'say_quantity' => 13,
            'calculated_quantity' => 12.5,
            'calculated_hours' => 2.5,
            'calculated_kilometers' => 0,
            'fuel_factor_km' => 0,
            'fuel_factor_hr' => 5,
            'date_from' => '2026-03-01',
            'date_to' => '2026-03-01',
            'status' => 'PEND',
            'actual_quantity' => 0,
        ]);
    }

    public function test_unprocessed_entries_contains_unbudgeted_instead_of_reference()
    {
        $user = User::factory()->create(['role' => 'data_logger']);
        $type = AssetType::create(['name' => 'Vehicle']);
        $account = ChargeableAccount::create(['name' => 'Project Alpha', 'status' => 'Active']);
        $asset = Asset::create([
            'fleet_no' => 'V-TEST',
            'asset_type_id' => $type->id,
            'fuel_factor_km' => 2.5,
            'tank_capacity' => 100,
        ]);

        UtilizationEntry::create([
            'asset_id' => $asset->id,
            'date' => '2026-03-01',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'reference' => 'REF-TEST',
            'particulars' => 'Test',
            'start_kilometer_reading' => 1000,
            'end_kilometer_reading' => 1100,
            'driver_operator_name' => 'Tester',
            'chargeable_account_id' => $account->id,
            'calculation_type' => 'Kilometer Reading',
            'unbudgeted' => true,
        ]);

        Livewire::actingAs($user)
            ->test(CreateFuelOrder::class)
            ->set('asset_id', $asset->id)
            ->set('date_from', '2026-03-01')
            ->set('date_to', '2026-03-01')
            ->assertSet('unprocessed_entries_count', 1)
            ->assertViewHas('unprocessed_entries', function($entries) {
                return count($entries) === 1 && 
                       $entries[0]['unbudgeted'] === true && 
                       !isset($entries[0]['reference']);
            });
    }

    public function test_user_can_actualize_fuel_order()
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
        $user = User::factory()->create(['role' => 'administrator']);
        $type = AssetType::create(['name' => 'Vehicle']);
        $asset = Asset::create([
            'fleet_no' => 'V-001',
            'asset_type_id' => $type->id,
            'fuel_factor_km' => 2.5,
            'fuel_factor_hr' => 1.5,
            'tank_capacity' => 100,
        ]);
        
        $fuelOrder = FuelOrder::create([
            'asset_id' => $asset->id,
            'calculated_quantity' => 80,
            'say_quantity' => 80,
            'status' => 'PEND',
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->post(route('fuel-orders.store-actualization', $fuelOrder), [
            'actual_quantity' => 495.5,
        ]);

        $response->assertRedirect(route('fuel-orders.index'));
        $this->assertDatabaseHas('fuel_orders', [
            'id' => $fuelOrder->id,
            'say_quantity' => 80.0,
            'actual_quantity' => 495.5,
            'status' => 'DONE',
        ]);
    }

    public function test_administrator_can_edit_fuel_order()
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
        $user = User::factory()->create(['role' => 'administrator']);
        $type = AssetType::create(['name' => 'Vehicle']);
        $asset = Asset::create([
            'fleet_no' => 'V-001',
            'asset_type_id' => $type->id,
            'fuel_factor_km' => 2.5,
            'tank_capacity' => 100,
        ]);
        
        $fuelOrder = FuelOrder::create([
            'asset_id' => $asset->id,
            'calculated_quantity' => 80,
            'say_quantity' => 80,
            'status' => 'PEND',
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->put(route('fuel-orders.update', $fuelOrder), [
            'say_quantity' => 90.5,
            'actual_quantity' => 85.0,
            'status' => 'DONE',
        ]);

        $response->assertRedirect(route('fuel-orders.index'));
        $this->assertDatabaseHas('fuel_orders', [
            'id' => $fuelOrder->id,
            'say_quantity' => 90.5,
            'actual_quantity' => 85.0,
            'status' => 'DONE',
        ]);
    }

    public function test_updating_fuel_order_to_void_releases_utilization_entries()
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
        $user = User::factory()->create(['role' => 'administrator']);
        $type = AssetType::create(['name' => 'Vehicle']);
        $account = ChargeableAccount::create(['name' => 'Account A', 'status' => 'Active']);
        
        $asset = Asset::create([
            'fleet_no' => 'V-001',
            'asset_type_id' => $type->id,
            'fuel_factor_km' => 2.5,
            'fuel_factor_hr' => 0,
            'tank_capacity' => 100,
        ]);
        
        $fuelOrder = FuelOrder::create([
            'asset_id' => $asset->id,
            'calculated_quantity' => 80,
            'say_quantity' => 80,
            'status' => 'PEND',
            'created_by' => $user->id,
        ]);

        $entry = UtilizationEntry::create([
            'asset_id' => $asset->id,
            'date' => '2026-03-01',
            'start_time' => '08:00',
            'end_time' => '17:00',
            'reference' => 'REF-VOID',
            'particulars' => 'Test',
            'driver_operator_name' => 'John',
            'start_kilometer_reading' => 1000,
            'end_kilometer_reading' => 1100,
            'calculation_type' => 'Kilometer Reading',
            'chargeable_account_id' => $account->id,
            'fuel_order_id' => $fuelOrder->id,
        ]);

        $this->assertEquals($fuelOrder->id, $entry->fresh()->fuel_order_id);

        $response = $this->actingAs($user)->post(route('fuel-orders.void', $fuelOrder));

        $response->assertRedirect(route('fuel-orders.index'));

        $this->assertDatabaseHas('fuel_orders', [
            'id' => $fuelOrder->id,
            'status' => 'VOID',
        ]);

        $this->assertNull($entry->fresh()->fuel_order_id);
    }
}
