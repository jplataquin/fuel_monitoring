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
}
