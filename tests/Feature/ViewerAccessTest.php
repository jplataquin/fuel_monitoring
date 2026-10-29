<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetType;
use App\Models\ChargeableAccount;
use App\Models\FuelOrder;
use App\Models\SubAccountBudget;
use App\Models\User;
use App\Models\UtilizationEntry;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ViewerAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([ValidateCsrfToken::class]);
    }

    public function test_viewer_can_view_dashboard(): void
    {
        $viewer = User::factory()->create(['role' => 'viewer']);

        $response = $this->actingAs($viewer)->get(route('dashboard'));

        $response->assertStatus(200);
    }

    public function test_viewer_can_view_all_reports(): void
    {
        $viewer = User::factory()->create(['role' => 'viewer']);
        $account = ChargeableAccount::create([
            'name' => 'Report Account',
            'classification' => 'Running',
            'status' => 'Active',
        ]);

        $this->actingAs($viewer)->get(route('reports.asset-utilization'))->assertStatus(200);
        $this->actingAs($viewer)->get(route('reports.fuel-orders'))->assertStatus(200);
        $this->actingAs($viewer)->get(route('reports.chargeable-accounts'))->assertStatus(200);
        $this->actingAs($viewer)->get(route('dashboard.sub-accounts', $account))->assertStatus(200);
    }

    public function test_viewer_can_view_assets_and_logs_but_cannot_mutate(): void
    {
        $viewer = User::factory()->create(['role' => 'viewer']);
        $type = AssetType::create(['name' => 'Van']);
        $asset = Asset::create([
            'fleet_no' => 'VAN-01',
            'asset_type_id' => $type->id,
            'fuel_type' => 'Diesel',
            'tank_capacity' => 70,
            'fuel_factor_km' => 8.5,
            'fuel_factor_hr' => 2.5,
            'status' => 'Active',
        ]);

        // Can view
        $this->actingAs($viewer)->get(route('assets.index'))->assertStatus(200);
        $this->actingAs($viewer)->get(route('assets.show', $asset))->assertStatus(200);
        $this->actingAs($viewer)->get(route('assets.logs', $asset))->assertStatus(200);
        $this->actingAs($viewer)->get(route('assets.logs.print', $asset))->assertStatus(200);

        // Cannot mutate
        $this->actingAs($viewer)->get(route('assets.create'))->assertStatus(403);
        $this->actingAs($viewer)->post(route('assets.store'), [
            'fleet_no' => 'VAN-02',
            'asset_type_id' => $type->id,
            'fuel_type' => 'Diesel',
        ])->assertStatus(403);
        $this->actingAs($viewer)->get(route('assets.edit', $asset))->assertStatus(403);
        $this->actingAs($viewer)->put(route('assets.update', $asset), [
            'fleet_no' => 'VAN-UPDATED',
        ])->assertStatus(403);
        $this->actingAs($viewer)->delete(route('assets.destroy', $asset))->assertStatus(403);

        // Cannot access or execute bulk upload
        $this->actingAs($viewer)->get(route('assets.utilization-entries.bulk-upload', $asset))->assertStatus(403);
        $this->actingAs($viewer)->post(route('assets.utilization-entries.bulk-preview', $asset))->assertStatus(403);
        $this->actingAs($viewer)->post(route('assets.utilization-entries.bulk-store', $asset), ['rows' => []])->assertStatus(403);
        $this->actingAs($viewer)->get(route('assets.utilization-entries.bulk-template', $asset))->assertStatus(403);
        $this->actingAs($viewer)->post(route('assets.utilization-entries.bulk-upload-chunk', $asset))->assertStatus(403);
    }

    public function test_viewer_can_view_utilization_entries_but_cannot_mutate(): void
    {
        $viewer = User::factory()->create(['role' => 'viewer']);
        $admin = User::factory()->create(['role' => 'administrator']);
        $type = AssetType::create(['name' => 'Truck']);
        $account = ChargeableAccount::create(['name' => 'Field Account', 'classification' => 'Running', 'status' => 'Active']);
        $subAccount = $account->subAccounts()->create(['name' => 'Sub Field']);

        $asset = Asset::create([
            'fleet_no' => 'TRK-01',
            'asset_type_id' => $type->id,
            'fuel_type' => 'Diesel',
            'tank_capacity' => 100,
            'fuel_factor_km' => 5.0,
            'fuel_factor_hr' => 2.0,
            'status' => 'Active',
        ]);

        $entry = UtilizationEntry::create([
            'asset_id' => $asset->id,
            'chargeable_account_id' => $account->id,
            'sub_account_id' => $subAccount->id,
            'reference' => 'REF-001',
            'particulars' => 'Daily transport',
            'driver_operator_name' => 'John Doe',
            'date' => now()->toDateString(),
            'start_km' => 100,
            'end_km' => 150,
            'start_time' => now()->startOfDay()->addHours(8),
            'end_time' => now()->startOfDay()->addHours(12),
            'fuel_factor_km' => 5.0,
            'fuel_factor_hr' => 2.0,
            'calculation_type' => 'distance',
            'created_by' => $admin->id,
        ]);

        // Can view
        $this->actingAs($viewer)->get(route('utilization-entries.index'))->assertStatus(200);
        $this->actingAs($viewer)->get(route('utilization-entries.show', $entry))->assertStatus(200);
        $this->actingAs($viewer)->get(route('utilization-entries.print'))->assertStatus(200);

        // Cannot mutate
        $this->actingAs($viewer)->post(route('utilization-entries.store'), [
            'asset_id' => $asset->id,
            'chargeable_account_id' => $account->id,
            'sub_account_id' => $subAccount->id,
            'date' => now()->toDateString(),
            'start_km' => 150,
            'end_km' => 200,
        ])->assertStatus(403);

        $this->actingAs($viewer)->get(route('utilization-entries.edit', $entry))->assertStatus(403);
        $this->actingAs($viewer)->patch(route('utilization-entries.update', $entry), [
            'start_km' => 110,
        ])->assertStatus(403);
        $this->actingAs($viewer)->delete(route('utilization-entries.destroy', $entry))->assertStatus(403);
    }

    public function test_viewer_can_view_fuel_orders_but_cannot_mutate(): void
    {
        $viewer = User::factory()->create(['role' => 'viewer']);
        $admin = User::factory()->create(['role' => 'administrator']);
        $type = AssetType::create(['name' => 'Sedan']);
        $asset = Asset::create([
            'fleet_no' => 'SED-01',
            'asset_type_id' => $type->id,
            'fuel_type' => 'Gasoline',
            'tank_capacity' => 50,
            'status' => 'Active',
        ]);

        $order = FuelOrder::create([
            'order_no' => 'FO-9999',
            'asset_id' => $asset->id,
            'fuel_type' => 'Gasoline',
            'requested_fuel' => 40,
            'status' => 'PEND',
            'created_by' => $admin->id,
        ]);

        // Can view
        $this->actingAs($viewer)->get(route('fuel-orders.index'))->assertStatus(200);
        $this->actingAs($viewer)->get(route('fuel-orders.show', $order))->assertStatus(200);
        $this->actingAs($viewer)->get(route('fuel-orders.print'))->assertStatus(200);

        // Cannot mutate
        $this->actingAs($viewer)->get(route('fuel-orders.create'))->assertStatus(403);
        $this->actingAs($viewer)->post(route('fuel-orders.store'), [
            'asset_id' => $asset->id,
            'requested_fuel' => 30,
        ])->assertStatus(403);
        $this->actingAs($viewer)->get(route('fuel-orders.edit', $order))->assertStatus(403);
        $this->actingAs($viewer)->patch(route('fuel-orders.update', $order), [
            'requested_fuel' => 35,
        ])->assertStatus(403);
        $this->actingAs($viewer)->get(route('fuel-orders.actualize', $order))->assertStatus(403);
        $this->actingAs($viewer)->post(route('fuel-orders.store-actualization', $order), [
            'actual_fuel' => 38,
        ])->assertStatus(403);
        $this->actingAs($viewer)->post(route('fuel-orders.void', $order), [
            'remarks' => 'Void order',
        ])->assertStatus(403);
        $this->actingAs($viewer)->post(route('fuel-orders.approve-waiver', $order))->assertStatus(403);
        $this->actingAs($viewer)->post(route('fuel-orders.unlink-sub-account', $order))->assertStatus(403);
    }

    public function test_viewer_can_view_accounts_and_sub_accounts_but_cannot_mutate(): void
    {
        $viewer = User::factory()->create(['role' => 'viewer']);
        $account = ChargeableAccount::create([
            'name' => 'Viewer Project',
            'classification' => 'Running',
            'status' => 'Active',
        ]);
        $subAccount = $account->subAccounts()->create(['name' => 'Sub Viewer', 'quantity' => 100, 'unit' => 'm']);

        // Can view
        $this->actingAs($viewer)->get(route('chargeable-accounts.index'))->assertStatus(200);
        $this->actingAs($viewer)->get(route('chargeable-accounts.show', $account))->assertStatus(200);
        $this->actingAs($viewer)->get(route('chargeable-accounts.print', $account))->assertStatus(200);
        $this->actingAs($viewer)->get(route('sub-accounts.show', $subAccount))->assertStatus(200);

        // Cannot mutate chargeable accounts
        $this->actingAs($viewer)->get(route('chargeable-accounts.create'))->assertStatus(403);
        $this->actingAs($viewer)->post(route('chargeable-accounts.store'), [
            'name' => 'Forbidden Project',
            'classification' => 'Running',
            'status' => 'Active',
        ])->assertStatus(403);
        $this->actingAs($viewer)->get(route('chargeable-accounts.edit', $account))->assertStatus(403);
        $this->actingAs($viewer)->patch(route('chargeable-accounts.update', $account), [
            'name' => 'Forbidden Edit',
        ])->assertStatus(403);
        $this->actingAs($viewer)->delete(route('chargeable-accounts.destroy', $account))->assertStatus(403);

        // Cannot mutate sub accounts
        $this->actingAs($viewer)->post(route('chargeable-accounts.sub-accounts.store', $account), [
            'name' => 'Forbidden Sub',
        ])->assertStatus(403);
        $this->actingAs($viewer)->get(route('sub-accounts.edit', $subAccount))->assertStatus(403);
        $this->actingAs($viewer)->patch(route('sub-accounts.update', $subAccount), [
            'name' => 'Forbidden Sub Edit',
        ])->assertStatus(403);
        $this->actingAs($viewer)->delete(route('sub-accounts.destroy', $subAccount))->assertStatus(403);

        // Cannot mutate accomplishments
        $this->actingAs($viewer)->post(route('sub-accounts.accomplishments.store', $subAccount), [
            'quantity' => 20,
            'date_at' => now()->toDateString(),
        ])->assertStatus(403);

        // Cannot merge sub accounts
        $this->actingAs($viewer)->post(route('sub-accounts.merge', $subAccount))->assertStatus(403);
    }

    public function test_viewer_can_view_account_budgets_but_cannot_mutate(): void
    {
        $viewer = User::factory()->create(['role' => 'viewer']);
        $admin = User::factory()->create(['role' => 'administrator']);
        $account = ChargeableAccount::create(['name' => 'Budget Account', 'classification' => 'Running', 'status' => 'Active']);
        $subAccount = $account->subAccounts()->create(['name' => 'Budget Sub']);

        $budget = SubAccountBudget::create([
            'sub_account_id' => $subAccount->id,
            'budget_quantity' => 500,
            'status' => 'Pending',
            'created_by' => $admin->id,
        ]);

        // Can view
        $this->actingAs($viewer)->get(route('account-budgets.index'))->assertStatus(200);
        $this->actingAs($viewer)->get(route('account-budgets.show', $budget))->assertStatus(200);
        $this->actingAs($viewer)->get(route('account-budgets.print'))->assertStatus(200);

        // Cannot mutate
        $this->actingAs($viewer)->post(route('account-budgets.store'), [
            'sub_account_id' => $subAccount->id,
            'budget_quantity' => 200,
        ])->assertStatus(403);
        $this->actingAs($viewer)->get(route('account-budgets.edit', $budget))->assertStatus(403);
        $this->actingAs($viewer)->patch(route('account-budgets.update', $budget), [
            'sub_account_id' => $subAccount->id,
            'budget_quantity' => 600,
        ])->assertStatus(403);
        $this->actingAs($viewer)->delete(route('account-budgets.destroy', $budget))->assertStatus(403);
        $this->actingAs($viewer)->patch(route('account-budgets.approve', $budget))->assertStatus(403);
        $this->actingAs($viewer)->patch(route('account-budgets.reject', $budget))->assertStatus(403);
    }

    public function test_viewer_cannot_access_settings(): void
    {
        $viewer = User::factory()->create(['role' => 'viewer']);

        $this->actingAs($viewer)->get(route('settings.index'))->assertStatus(403);
        $this->actingAs($viewer)->get(route('mcp-tokens.index'))->assertStatus(403);
        $this->actingAs($viewer)->post(route('mcp-tokens.store'))->assertStatus(403);
        $this->actingAs($viewer)->get(route('users.index'))->assertStatus(403);
        $this->actingAs($viewer)->get(route('users.create-viewer'))->assertStatus(403);
        $this->actingAs($viewer)->post(route('users.store'))->assertStatus(403);
        $this->actingAs($viewer)->get(route('asset-types.index'))->assertStatus(403);
        $this->actingAs($viewer)->post(route('asset-types.store'))->assertStatus(403);
        $this->actingAs($viewer)->get(route('public-dashboard-links.index'))->assertStatus(403);
        $this->actingAs($viewer)->post(route('public-dashboard-links.store'))->assertStatus(403);
    }

    public function test_administrator_can_create_and_manage_viewer(): void
    {
        $admin = User::factory()->create(['role' => 'administrator']);

        // Access creation page
        $response = $this->actingAs($admin)->get(route('users.create-viewer'));
        $response->assertStatus(200);
        $response->assertSee('Add New Access: Viewer');

        // Store viewer
        $response = $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Auditor Jane',
            'email' => 'jane@example.com',
            'role' => 'viewer',
            'password' => 'secret12345',
        ]);
        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', ['email' => 'jane@example.com', 'role' => 'viewer']);

        // Update user to viewer
        $user = User::factory()->create(['role' => 'data_logger']);
        $response = $this->actingAs($admin)->patch(route('users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'role' => 'viewer',
        ]);
        $response->assertRedirect(route('users.index'));
        $this->assertEquals('viewer', $user->fresh()->role);
    }

    public function test_navigation_renders_accounts_and_budget_for_viewer_but_not_settings(): void
    {
        $viewer = User::factory()->create(['role' => 'viewer']);

        $response = $this->actingAs($viewer)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee(route('chargeable-accounts.index'));
        $response->assertSee(route('account-budgets.index'));
        $response->assertDontSee(route('settings.index'));
    }
}
