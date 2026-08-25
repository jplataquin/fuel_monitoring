<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetType;
use App\Models\ChargeableAccount;
use App\Models\FuelOrder;
use App\Models\SubAccount;
use App\Models\User;
use App\Models\UtilizationEntry;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UtilizationEntryFeatureTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Asset $asset;

    private AssetType $assetType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'administrator']);
        $this->assetType = AssetType::create(['name' => 'Vehicle']);
        $this->asset = Asset::create([
            'fleet_no' => 'V-100',
            'asset_type_id' => $this->assetType->id,
            'fuel_factor_km' => 2.5,
            'fuel_factor_hr' => 1.5,
            'tank_capacity' => 100,
        ]);
    }

    public function test_scoped_account_allows_date_within_scope_on_create(): void
    {
        $this->withoutMiddleware([ValidateCsrfToken::class]);

        $account = ChargeableAccount::create([
            'name' => 'Scoped Project',
            'classification' => 'Scoped',
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-30',
            'status' => 'Active',
        ]);

        $subAccount = SubAccount::create([
            'chargeable_account_id' => $account->id,
            'name' => 'Sub 1',
        ]);

        $response = $this->actingAs($this->admin)->post(route('utilization-entries.store'), [
            'asset_id' => $this->asset->id,
            'date' => '2026-06-15',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'driver_operator_name' => 'John Operator',
            'chargeable_account_id' => $account->id,
            'sub_account_id' => $subAccount->id,
            'reference' => 'REF-123',
            'calculation_type' => 'Timeframe',
            'particulars' => 'Daily run',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('utilization_entries', [
            'asset_id' => $this->asset->id,
            'date' => '2026-06-15 00:00:00',
            'chargeable_account_id' => $account->id,
        ]);
    }

    public function test_scoped_account_rejects_date_before_scope_on_create(): void
    {
        $this->withoutMiddleware([ValidateCsrfToken::class]);

        $account = ChargeableAccount::create([
            'name' => 'Scoped Project',
            'classification' => 'Scoped',
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-30',
            'status' => 'Active',
        ]);

        $subAccount = SubAccount::create([
            'chargeable_account_id' => $account->id,
            'name' => 'Sub 1',
        ]);

        $response = $this->actingAs($this->admin)->post(route('utilization-entries.store'), [
            'asset_id' => $this->asset->id,
            'date' => '2026-05-31',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'driver_operator_name' => 'John Operator',
            'chargeable_account_id' => $account->id,
            'sub_account_id' => $subAccount->id,
            'reference' => 'REF-123',
            'calculation_type' => 'Timeframe',
            'particulars' => 'Daily run',
        ]);

        $response->assertSessionHasErrors(['date']);
        $this->assertDatabaseMissing('utilization_entries', [
            'asset_id' => $this->asset->id,
            'date' => '2026-05-31',
        ]);
    }

    public function test_scoped_account_rejects_date_after_scope_on_create(): void
    {
        $this->withoutMiddleware([ValidateCsrfToken::class]);

        $account = ChargeableAccount::create([
            'name' => 'Scoped Project',
            'classification' => 'Scoped',
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-30',
            'status' => 'Active',
        ]);

        $subAccount = SubAccount::create([
            'chargeable_account_id' => $account->id,
            'name' => 'Sub 1',
        ]);

        $response = $this->actingAs($this->admin)->post(route('utilization-entries.store'), [
            'asset_id' => $this->asset->id,
            'date' => '2026-07-01',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'driver_operator_name' => 'John Operator',
            'chargeable_account_id' => $account->id,
            'sub_account_id' => $subAccount->id,
            'reference' => 'REF-123',
            'calculation_type' => 'Timeframe',
            'particulars' => 'Daily run',
        ]);

        $response->assertSessionHasErrors(['date']);
        $this->assertDatabaseMissing('utilization_entries', [
            'asset_id' => $this->asset->id,
            'date' => '2026-07-01',
        ]);
    }

    public function test_running_account_allows_any_date_on_create(): void
    {
        $this->withoutMiddleware([ValidateCsrfToken::class]);

        $account = ChargeableAccount::create([
            'name' => 'Running Project',
            'classification' => 'Running',
            'status' => 'Active',
        ]);

        $subAccount = SubAccount::create([
            'chargeable_account_id' => $account->id,
            'name' => 'Sub 1',
        ]);

        $response = $this->actingAs($this->admin)->post(route('utilization-entries.store'), [
            'asset_id' => $this->asset->id,
            'date' => '2026-01-01',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'driver_operator_name' => 'John Operator',
            'chargeable_account_id' => $account->id,
            'sub_account_id' => $subAccount->id,
            'reference' => 'REF-123',
            'calculation_type' => 'Timeframe',
            'particulars' => 'Daily run',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('utilization_entries', [
            'asset_id' => $this->asset->id,
            'date' => '2026-01-01 00:00:00',
        ]);
    }

    public function test_scoped_account_allows_date_within_scope_on_update(): void
    {
        $this->withoutMiddleware([ValidateCsrfToken::class]);

        $account = ChargeableAccount::create([
            'name' => 'Scoped Project',
            'classification' => 'Scoped',
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-30',
            'status' => 'Active',
        ]);

        $subAccount = SubAccount::create([
            'chargeable_account_id' => $account->id,
            'name' => 'Sub 1',
        ]);

        $entry = UtilizationEntry::create([
            'asset_id' => $this->asset->id,
            'date' => '2026-06-10',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'driver_operator_name' => 'John Operator',
            'chargeable_account_id' => $account->id,
            'sub_account_id' => $subAccount->id,
            'reference' => 'REF-123',
            'calculation_type' => 'Timeframe',
            'particulars' => 'Daily run',
        ]);

        $response = $this->actingAs($this->admin)->patch(route('utilization-entries.update', $entry), [
            'date' => '2026-06-20',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'driver_operator_name' => 'John Operator',
            'chargeable_account_id' => $account->id,
            'sub_account_id' => $subAccount->id,
            'reference' => 'REF-123',
            'calculation_type' => 'Timeframe',
            'particulars' => 'Daily run',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('utilization_entries', [
            'id' => $entry->id,
            'date' => '2026-06-20 00:00:00',
        ]);
    }

    public function test_scoped_account_rejects_date_before_scope_on_update(): void
    {
        $this->withoutMiddleware([ValidateCsrfToken::class]);

        $account = ChargeableAccount::create([
            'name' => 'Scoped Project',
            'classification' => 'Scoped',
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-30',
            'status' => 'Active',
        ]);

        $subAccount = SubAccount::create([
            'chargeable_account_id' => $account->id,
            'name' => 'Sub 1',
        ]);

        $entry = UtilizationEntry::create([
            'asset_id' => $this->asset->id,
            'date' => '2026-06-10',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'driver_operator_name' => 'John Operator',
            'chargeable_account_id' => $account->id,
            'sub_account_id' => $subAccount->id,
            'reference' => 'REF-123',
            'calculation_type' => 'Timeframe',
            'particulars' => 'Daily run',
        ]);

        $response = $this->actingAs($this->admin)->patch(route('utilization-entries.update', $entry), [
            'date' => '2026-05-31',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'driver_operator_name' => 'John Operator',
            'chargeable_account_id' => $account->id,
            'sub_account_id' => $subAccount->id,
            'reference' => 'REF-123',
            'calculation_type' => 'Timeframe',
            'particulars' => 'Daily run',
        ]);

        $response->assertSessionHasErrors(['date']);
        $this->assertDatabaseHas('utilization_entries', [
            'id' => $entry->id,
            'date' => '2026-06-10 00:00:00', // remained unchanged
        ]);
    }

    public function test_scoped_account_rejects_date_after_scope_on_update(): void
    {
        $this->withoutMiddleware([ValidateCsrfToken::class]);

        $account = ChargeableAccount::create([
            'name' => 'Scoped Project',
            'classification' => 'Scoped',
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-30',
            'status' => 'Active',
        ]);

        $subAccount = SubAccount::create([
            'chargeable_account_id' => $account->id,
            'name' => 'Sub 1',
        ]);

        $entry = UtilizationEntry::create([
            'asset_id' => $this->asset->id,
            'date' => '2026-06-10',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'driver_operator_name' => 'John Operator',
            'chargeable_account_id' => $account->id,
            'sub_account_id' => $subAccount->id,
            'reference' => 'REF-123',
            'calculation_type' => 'Timeframe',
            'particulars' => 'Daily run',
        ]);

        $response = $this->actingAs($this->admin)->patch(route('utilization-entries.update', $entry), [
            'date' => '2026-07-01',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'driver_operator_name' => 'John Operator',
            'chargeable_account_id' => $account->id,
            'sub_account_id' => $subAccount->id,
            'reference' => 'REF-123',
            'calculation_type' => 'Timeframe',
            'particulars' => 'Daily run',
        ]);

        $response->assertSessionHasErrors(['date']);
        $this->assertDatabaseHas('utilization_entries', [
            'id' => $entry->id,
            'date' => '2026-06-10 00:00:00', // remained unchanged
        ]);
    }

    public function test_actual_hours_calculation_type_requires_gt_zero_actual_hours(): void
    {
        $this->withoutMiddleware([ValidateCsrfToken::class]);

        $account = ChargeableAccount::create([
            'name' => 'Running Project',
            'classification' => 'Running',
            'status' => 'Active',
        ]);

        $subAccount = SubAccount::create([
            'chargeable_account_id' => $account->id,
            'name' => 'Sub 1',
        ]);

        // 1. Fails when actual_hours is empty
        $response = $this->actingAs($this->admin)->post(route('utilization-entries.store'), [
            'asset_id' => $this->asset->id,
            'date' => '2026-06-15',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'driver_operator_name' => 'John Operator',
            'chargeable_account_id' => $account->id,
            'sub_account_id' => $subAccount->id,
            'reference' => 'REF-123',
            'calculation_type' => 'Actual Hours',
            'particulars' => 'Daily run',
        ]);
        $response->assertSessionHasErrors(['actual_hours']);

        // 2. Fails when actual_hours is 0
        $response = $this->actingAs($this->admin)->post(route('utilization-entries.store'), [
            'asset_id' => $this->asset->id,
            'date' => '2026-06-15',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'driver_operator_name' => 'John Operator',
            'chargeable_account_id' => $account->id,
            'sub_account_id' => $subAccount->id,
            'reference' => 'REF-123',
            'calculation_type' => 'Actual Hours',
            'particulars' => 'Daily run',
            'actual_hours' => 0,
        ]);
        $response->assertSessionHasErrors(['actual_hours']);

        // 3. Fails when actual_hours is negative
        $response = $this->actingAs($this->admin)->post(route('utilization-entries.store'), [
            'asset_id' => $this->asset->id,
            'date' => '2026-06-15',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'driver_operator_name' => 'John Operator',
            'chargeable_account_id' => $account->id,
            'sub_account_id' => $subAccount->id,
            'reference' => 'REF-123',
            'calculation_type' => 'Actual Hours',
            'particulars' => 'Daily run',
            'actual_hours' => -5.5,
        ]);
        $response->assertSessionHasErrors(['actual_hours']);
    }

    public function test_actual_hours_calculation_type_saves_correctly(): void
    {
        $this->withoutMiddleware([ValidateCsrfToken::class]);

        $account = ChargeableAccount::create([
            'name' => 'Running Project',
            'classification' => 'Running',
            'status' => 'Active',
        ]);

        $subAccount = SubAccount::create([
            'chargeable_account_id' => $account->id,
            'name' => 'Sub 1',
        ]);

        $response = $this->actingAs($this->admin)->post(route('utilization-entries.store'), [
            'asset_id' => $this->asset->id,
            'date' => '2026-06-15',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'driver_operator_name' => 'John Operator',
            'chargeable_account_id' => $account->id,
            'sub_account_id' => $subAccount->id,
            'reference' => 'REF-123',
            'calculation_type' => 'Actual Hours',
            'particulars' => 'Daily run',
            'actual_hours' => 4.5,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('utilization_entries', [
            'asset_id' => $this->asset->id,
            'calculation_type' => 'Actual Hours',
            'actual_hours' => 4.5,
        ]);
    }

    public function test_actual_hours_allows_start_and_end_time_to_be_equal(): void
    {
        $this->withoutMiddleware([ValidateCsrfToken::class]);

        $account = ChargeableAccount::create([
            'name' => 'Running Project',
            'classification' => 'Running',
            'status' => 'Active',
        ]);

        $subAccount = SubAccount::create([
            'chargeable_account_id' => $account->id,
            'name' => 'Sub 1',
        ]);

        // 1. For Actual Hours, identical start and end time is ALLOWED
        $response = $this->actingAs($this->admin)->post(route('utilization-entries.store'), [
            'asset_id' => $this->asset->id,
            'date' => '2026-06-15',
            'start_time' => '08:00',
            'end_time' => '08:00',
            'driver_operator_name' => 'John Operator',
            'chargeable_account_id' => $account->id,
            'sub_account_id' => $subAccount->id,
            'reference' => 'REF-123',
            'calculation_type' => 'Actual Hours',
            'particulars' => 'Daily run',
            'actual_hours' => 4.5,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('utilization_entries', [
            'asset_id' => $this->asset->id,
            'calculation_type' => 'Actual Hours',
            'start_time' => '08:00',
            'end_time' => '08:00',
        ]);

        // 2. For Timeframe, identical start and end time is REJECTED
        $response = $this->actingAs($this->admin)->post(route('utilization-entries.store'), [
            'asset_id' => $this->asset->id,
            'date' => '2026-06-15',
            'start_time' => '08:00',
            'end_time' => '08:00',
            'driver_operator_name' => 'John Operator',
            'chargeable_account_id' => $account->id,
            'sub_account_id' => $subAccount->id,
            'reference' => 'REF-123',
            'calculation_type' => 'Timeframe',
            'particulars' => 'Daily run',
        ]);

        $response->assertSessionHasErrors(['end_time']);
    }

    public function test_utilization_entries_index_filters_by_chargeable_account_and_sub_account(): void
    {
        $account1 = ChargeableAccount::create([
            'name' => 'Account A',
            'classification' => 'Running',
            'status' => 'Active',
        ]);
        $account2 = ChargeableAccount::create([
            'name' => 'Account B',
            'classification' => 'Running',
            'status' => 'Active',
        ]);

        $subAccount1 = SubAccount::create([
            'chargeable_account_id' => $account1->id,
            'name' => 'Sub A',
        ]);
        $subAccount2 = SubAccount::create([
            'chargeable_account_id' => $account2->id,
            'name' => 'Sub B',
        ]);

        // Entry for account 1 / sub 1
        $entry1 = UtilizationEntry::create([
            'asset_id' => $this->asset->id,
            'date' => '2026-08-15',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'driver_operator_name' => 'Operator A',
            'chargeable_account_id' => $account1->id,
            'sub_account_id' => $subAccount1->id,
            'reference' => 'REF-A',
            'calculation_type' => 'Timeframe',
            'particulars' => 'First run',
        ]);

        // Entry for account 2 / sub 2
        $entry2 = UtilizationEntry::create([
            'asset_id' => $this->asset->id,
            'date' => '2026-08-15',
            'start_time' => '11:00',
            'end_time' => '13:00',
            'driver_operator_name' => 'Operator B',
            'chargeable_account_id' => $account2->id,
            'sub_account_id' => $subAccount2->id,
            'reference' => 'REF-B',
            'calculation_type' => 'Timeframe',
            'particulars' => 'Second run',
        ]);

        // Filter by account 1 and sub 1
        $response = $this->actingAs($this->admin)->get(route('utilization-entries.index', [
            'chargeable_account_id' => $account1->id,
            'sub_account_id' => $subAccount1->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Operator A');
        $response->assertDontSee('Operator B');
    }

    public function test_utilization_entries_index_filters_by_asset_and_shows_grand_total(): void
    {
        $account = ChargeableAccount::create([
            'name' => 'Account A',
            'classification' => 'Running',
            'status' => 'Active',
        ]);
        $subAccount = SubAccount::create([
            'chargeable_account_id' => $account->id,
            'name' => 'Sub A',
        ]);

        $asset2 = Asset::create([
            'fleet_no' => 'EX-123',
            'asset_type_id' => $this->asset->asset_type_id,
            'fuel_factor_km' => 0,
            'fuel_factor_hr' => 3.0,
            'tank_capacity' => 150,
        ]);

        // Entry 1 for this->asset
        $entry1 = UtilizationEntry::create([
            'asset_id' => $this->asset->id,
            'date' => '2026-08-15',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'driver_operator_name' => 'Operator A',
            'chargeable_account_id' => $account->id,
            'sub_account_id' => $subAccount->id,
            'reference' => 'REF-A',
            'calculation_type' => 'Timeframe',
            'particulars' => 'First run',
            'fuel_factor_hr' => $this->asset->fuel_factor_hr,
            'fuel_factor_km' => $this->asset->fuel_factor_km,
        ]);

        // Entry 2 for asset2
        $entry2 = UtilizationEntry::create([
            'asset_id' => $asset2->id,
            'date' => '2026-08-15',
            'start_time' => '11:00',
            'end_time' => '13:00',
            'driver_operator_name' => 'Operator B',
            'chargeable_account_id' => $account->id,
            'sub_account_id' => $subAccount->id,
            'reference' => 'REF-B',
            'calculation_type' => 'Timeframe',
            'particulars' => 'Second run',
            'fuel_factor_hr' => $asset2->fuel_factor_hr,
            'fuel_factor_km' => $asset2->fuel_factor_km,
        ]);

        // Filter by asset 2
        $response = $this->actingAs($this->admin)->get(route('utilization-entries.index', [
            'asset_id' => $asset2->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Operator B');
        $response->assertDontSee('Operator A');

        // Check the grand total is shown for entry 2 (2 hours * 3.0 = 6.00 liters)
        $response->assertSee('6.00');
    }

    public function test_utilization_entries_print_renders_correctly_with_filters(): void
    {
        $account = ChargeableAccount::create([
            'name' => 'Print Account',
            'classification' => 'Running',
            'status' => 'Active',
        ]);
        $subAccount = SubAccount::create([
            'chargeable_account_id' => $account->id,
            'name' => 'Print Sub',
        ]);

        // Entry for this->asset
        $entry = UtilizationEntry::create([
            'asset_id' => $this->asset->id,
            'date' => '2026-08-15',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'driver_operator_name' => 'Print Operator',
            'chargeable_account_id' => $account->id,
            'sub_account_id' => $subAccount->id,
            'reference' => 'REF-P',
            'calculation_type' => 'Timeframe',
            'particulars' => 'Print run',
            'fuel_factor_hr' => $this->asset->fuel_factor_hr,
            'fuel_factor_km' => $this->asset->fuel_factor_km,
        ]);

        $response = $this->actingAs($this->admin)->get(route('utilization-entries.print', [
            'chargeable_account_id' => $account->id,
            'sub_account_id' => $subAccount->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Utilization Entries List');
        $response->assertSee('Print Operator');
        $response->assertSee('Print Account');
        $response->assertSee('Print Sub');
        $response->assertSee('Total Consumed Fuel:');
        // Total consumed fuel is (2 hours * 1.5 = 3.00 L)
        $response->assertSee('3.00 L');
    }

    public function test_utilization_entry_edit_renders_prefilled_dropdowns()
    {
        $account = ChargeableAccount::create([
            'name' => 'Active Account',
            'status' => 'Active',
        ]);

        $sub = $account->subAccounts()->create([
            'name' => 'Sub Active',
        ]);

        $entry = UtilizationEntry::create([
            'asset_id' => $this->asset->id,
            'date' => '2026-06-10',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'driver_operator_name' => 'John Operator',
            'chargeable_account_id' => $account->id,
            'sub_account_id' => $sub->id,
            'reference' => 'REF-123',
            'calculation_type' => 'Timeframe',
            'particulars' => 'Daily run',
        ]);

        $response = $this->actingAs($this->admin)->get(route('utilization-entries.edit', $entry));
        $response->assertStatus(200);

        // Assert that Charged To is selected on Active Account
        $response->assertSee('value="'.$account->id.'"', false);
        $response->assertSee('selected', false);

        // Assert that Sub Account is rendered with Sub Active selected
        $response->assertSee('value="'.$sub->id.'"', false);
    }

    public function test_utilization_entries_index_shows_order_number_column(): void
    {
        $account = ChargeableAccount::create([
            'name' => 'Active Account',
            'status' => 'Active',
        ]);

        $sub = $account->subAccounts()->create([
            'name' => 'Sub Active',
        ]);

        // 1. Create a fuel order
        $fuelOrder = FuelOrder::create([
            'asset_id' => $this->asset->id,
            'chargeable_account_id' => $account->id,
            'sub_account_id' => $sub->id,
            'calculated_quantity' => 10.0,
            'status' => 'PEND',
        ]);

        // 2. Create utilization entry with fuel order
        $entryWithOrder = UtilizationEntry::create([
            'asset_id' => $this->asset->id,
            'date' => '2026-06-10',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'driver_operator_name' => 'John Operator',
            'chargeable_account_id' => $account->id,
            'sub_account_id' => $sub->id,
            'reference' => 'REF-123',
            'calculation_type' => 'Timeframe',
            'particulars' => 'Daily run',
            'fuel_order_id' => $fuelOrder->id,
        ]);

        // 3. Create utilization entry without fuel order
        $entryWithoutOrder = UtilizationEntry::create([
            'asset_id' => $this->asset->id,
            'date' => '2026-06-11',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'driver_operator_name' => 'Jane Operator',
            'chargeable_account_id' => $account->id,
            'sub_account_id' => $sub->id,
            'reference' => 'REF-456',
            'calculation_type' => 'Timeframe',
            'particulars' => 'Night run',
        ]);

        $response = $this->actingAs($this->admin)->get(route('utilization-entries.index'));
        $response->assertStatus(200);

        // Assert the column header exists
        $response->assertSee('Order #');

        // Assert the link to fuel order is present with correct padded format
        $expectedOrderNum = '#'.str_pad($fuelOrder->id, 5, '0', STR_PAD_LEFT);
        $response->assertSee(route('fuel-orders.show', $fuelOrder->id));
        $response->assertSee($expectedOrderNum);

        // Assert that the entry without order displays an em dash / dash
        $response->assertSee('—');
    }

    public function test_utilization_entry_can_be_deleted_if_not_assigned_to_fuel_order(): void
    {
        $this->withoutMiddleware([ValidateCsrfToken::class]);

        $account = ChargeableAccount::create([
            'name' => 'Active Account',
            'status' => 'Active',
        ]);

        $sub = $account->subAccounts()->create([
            'name' => 'Sub Active',
        ]);

        $entry = UtilizationEntry::create([
            'asset_id' => $this->asset->id,
            'date' => '2026-06-11',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'driver_operator_name' => 'Jane Operator',
            'chargeable_account_id' => $account->id,
            'sub_account_id' => $sub->id,
            'reference' => 'REF-456',
            'calculation_type' => 'Timeframe',
            'particulars' => 'Night run',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('utilization-entries.destroy', $entry));
        $response->assertRedirect(route('assets.show', $entry->asset_id));
        $response->assertSessionHas('status', 'Utilization entry deleted successfully.');

        $this->assertSoftDeleted('utilization_entries', ['id' => $entry->id]);
    }

    public function test_utilization_entry_cannot_be_deleted_if_assigned_to_fuel_order(): void
    {
        $this->withoutMiddleware([ValidateCsrfToken::class]);

        $account = ChargeableAccount::create([
            'name' => 'Active Account',
            'status' => 'Active',
        ]);

        $sub = $account->subAccounts()->create([
            'name' => 'Sub Active',
        ]);

        $fuelOrder = FuelOrder::create([
            'asset_id' => $this->asset->id,
            'chargeable_account_id' => $account->id,
            'sub_account_id' => $sub->id,
            'calculated_quantity' => 10.0,
            'status' => 'PEND',
        ]);

        $entry = UtilizationEntry::create([
            'asset_id' => $this->asset->id,
            'date' => '2026-06-10',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'driver_operator_name' => 'John Operator',
            'chargeable_account_id' => $account->id,
            'sub_account_id' => $sub->id,
            'reference' => 'REF-123',
            'calculation_type' => 'Timeframe',
            'particulars' => 'Daily run',
            'fuel_order_id' => $fuelOrder->id,
        ]);

        // Mock being on the utilization entry show page so redirect()->back() works
        $response = $this->actingAs($this->admin)
            ->from(route('utilization-entries.show', $entry))
            ->delete(route('utilization-entries.destroy', $entry));

        $response->assertRedirect(route('utilization-entries.show', $entry));
        $response->assertSessionHas('error', 'Cannot delete utilization entry because it is already assigned to a fuel order.');

        // Assert it is still in the database (not deleted)
        $this->assertDatabaseHas('utilization_entries', [
            'id' => $entry->id,
            'deleted_at' => null,
        ]);
    }

    public function test_utilization_entries_index_can_toggle_soft_deleted_entries(): void
    {
        $this->withoutMiddleware([ValidateCsrfToken::class]);

        $account = ChargeableAccount::create([
            'name' => 'Active Account',
            'status' => 'Active',
        ]);

        $sub = $account->subAccounts()->create([
            'name' => 'Sub Active',
        ]);

        // Create an active entry
        $activeEntry = UtilizationEntry::create([
            'asset_id' => $this->asset->id,
            'date' => '2026-06-11',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'driver_operator_name' => 'Active Operator',
            'chargeable_account_id' => $account->id,
            'sub_account_id' => $sub->id,
            'reference' => 'REF-ACTIVE',
            'calculation_type' => 'Timeframe',
            'particulars' => 'Active run',
        ]);

        // Create a deleted entry
        $deletedEntry = UtilizationEntry::create([
            'asset_id' => $this->asset->id,
            'date' => '2026-06-12',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'driver_operator_name' => 'Deleted Operator',
            'chargeable_account_id' => $account->id,
            'sub_account_id' => $sub->id,
            'reference' => 'REF-DELETED',
            'calculation_type' => 'Timeframe',
            'particulars' => 'Deleted run',
        ]);
        $deletedEntry->delete(); // Soft delete it

        // 1. By default, deleted entry should NOT be shown
        $response = $this->actingAs($this->admin)->get(route('utilization-entries.index'));
        $response->assertStatus(200);
        $response->assertSee('Active Operator');
        $response->assertDontSee('Deleted Operator');

        // 2. With include_deleted=1, both active and deleted should be shown
        $responseWithDeleted = $this->actingAs($this->admin)->get(route('utilization-entries.index', ['include_deleted' => '1']));
        $responseWithDeleted->assertStatus(200);
        $responseWithDeleted->assertSee('Active Operator');
        $responseWithDeleted->assertSee('Deleted Operator');
        $responseWithDeleted->assertSee('Deleted'); // Soft deleted badge is visible

        // 3. Print list with include_deleted=1
        $responsePrint = $this->actingAs($this->admin)->get(route('utilization-entries.print', ['include_deleted' => '1']));
        $responsePrint->assertStatus(200);
        $responsePrint->assertSee('Active Operator');
        $responsePrint->assertSee('Deleted Operator');
        $responsePrint->assertSee('DELETED');

        // 4. View soft deleted entry directly
        $responseShow = $this->actingAs($this->admin)->get(route('utilization-entries.show', $deletedEntry));
        $responseShow->assertStatus(200);
        $responseShow->assertSee('Deleted Operator');
        $responseShow->assertSee('Deleted'); // Header badge
    }
}
