<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetType;
use App\Models\ChargeableAccount;
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
}
