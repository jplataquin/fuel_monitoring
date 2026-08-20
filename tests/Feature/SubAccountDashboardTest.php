<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetType;
use App\Models\ChargeableAccount;
use App\Models\FuelOrder;
use App\Models\PublicDashboardLink;
use App\Models\SubAccount;
use App\Models\SubAccountBudget;
use App\Models\User;
use App\Models\UtilizationEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubAccountDashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Asset $asset;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'administrator']);

        $assetType = AssetType::create(['name' => 'Excavator']);
        $this->asset = Asset::create([
            'fleet_no' => 'EX-900',
            'asset_type_id' => $assetType->id,
            'fuel_factor_km' => 0,
            'fuel_factor_hr' => 2.0,
            'tank_capacity' => 200,
        ]);
    }

    public function test_user_can_access_sub_account_dashboard(): void
    {
        $account = ChargeableAccount::create([
            'name' => 'Main Account',
            'classification' => 'Running',
            'status' => 'Active',
        ]);

        $subAccount = SubAccount::create([
            'chargeable_account_id' => $account->id,
            'name' => 'Sub Active',
            'accomplishment' => 45.5,
        ]);

        SubAccountBudget::create([
            'sub_account_id' => $subAccount->id,
            'budget_quantity' => 1000,
            'status' => 'Approved',
            'allocated_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('dashboard.sub-accounts', $account));

        $response->assertStatus(200);
        $response->assertViewHasAll(['chargeableAccount', 'chartLabels', 'remainingBalances', 'subAccountData']);
        $response->assertSee('Sub Active');
        $response->assertSee('1,000.00'); // Check budget is shown

        $subAccountData = $response->viewData('subAccountData');
        $this->assertEquals(45.5, $subAccountData[0]['accomplishment']);

        // Check that Chart.js has datasets for both Fuel Consumption and Accomplishment
        $response->assertSee('accomplishmentValues');
        $response->assertSee('Fuel Consumption (%)');
        $response->assertSee('Accomplishment (%)');

        // Check that the breakdown table includes the Accomplishment (%) column and formatted value
        $response->assertSee('Accomplishment (%)');
        $response->assertSee('45.50%');
    }

    public function test_sub_account_dashboard_calculates_remaining_balance_correctly(): void
    {
        $account = ChargeableAccount::create([
            'name' => 'Main Project',
            'classification' => 'Running',
            'status' => 'Active',
        ]);

        $subAccount = SubAccount::create([
            'chargeable_account_id' => $account->id,
            'name' => 'Sub Section A',
        ]);

        SubAccountBudget::create([
            'sub_account_id' => $subAccount->id,
            'budget_quantity' => 1000,
            'status' => 'Approved',
            'allocated_by' => $this->admin->id,
        ]);

        // Fuel order with status DONE
        $order = FuelOrder::create([
            'asset_id' => $this->asset->id,
            'status' => 'DONE',
            'actual_quantity' => 200,
        ]);

        UtilizationEntry::create([
            'asset_id' => $this->asset->id,
            'date' => '2026-06-15',
            'start_time' => '08:00',
            'end_time' => '10:00', // 2 hours * factor 2.0 = 4 L calculated
            'fuel_factor_hr' => 2.0,
            'chargeable_account_id' => $account->id,
            'sub_account_id' => $subAccount->id,
            'fuel_order_id' => $order->id,
            'calculation_type' => 'Timeframe',
            'driver_operator_name' => 'Operator',
            'reference' => 'REF-XX',
            'particulars' => 'Work details',
        ]);

        $response = $this->actingAs($this->admin)->get(route('dashboard.sub-accounts', $account));

        $response->assertStatus(200);
        $subAccountData = $response->viewData('subAccountData');

        $this->assertEquals('Sub Section A', $subAccountData[0]['name']);
        $this->assertEquals(1000.0, $subAccountData[0]['total_budget']);
        $this->assertEquals(4.0, $subAccountData[0]['consumed']);
        $this->assertEquals(996.0, $subAccountData[0]['remaining']);
    }

    public function test_zero_budget_with_non_zero_consumption_is_exhausted(): void
    {
        $account = ChargeableAccount::create([
            'name' => 'Zero Budget Project',
            'classification' => 'Running',
            'status' => 'Active',
        ]);

        $subAccount = SubAccount::create([
            'chargeable_account_id' => $account->id,
            'name' => 'Sub Unbudgeted',
        ]);

        $order = FuelOrder::create([
            'asset_id' => $this->asset->id,
            'status' => 'DONE',
            'actual_quantity' => 100,
        ]);

        UtilizationEntry::create([
            'asset_id' => $this->asset->id,
            'date' => '2026-06-15',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'fuel_factor_hr' => 2.0,
            'chargeable_account_id' => $account->id,
            'sub_account_id' => $subAccount->id,
            'fuel_order_id' => $order->id,
            'calculation_type' => 'Timeframe',
            'driver_operator_name' => 'Operator',
            'reference' => 'REF-YY',
            'particulars' => 'Overage work',
        ]);

        $response = $this->actingAs($this->admin)->get(route('dashboard.sub-accounts', $account));

        $response->assertStatus(200);
        $subAccountData = $response->viewData('subAccountData');

        $this->assertEquals(0.0, $subAccountData[0]['total_budget']);
        $this->assertEquals(4.0, $subAccountData[0]['consumed']);

        $response->assertSee('Exhausted');
    }

    public function test_guest_can_access_public_sub_account_dashboard_with_active_slug(): void
    {
        $account = ChargeableAccount::create([
            'name' => 'Public Project',
            'classification' => 'Running',
            'status' => 'Active',
        ]);

        $subAccount = SubAccount::create([
            'chargeable_account_id' => $account->id,
            'name' => 'Public Sub Section',
        ]);

        SubAccountBudget::create([
            'sub_account_id' => $subAccount->id,
            'budget_quantity' => 500,
            'status' => 'Approved',
            'allocated_by' => $this->admin->id,
        ]);

        $link = PublicDashboardLink::create([
            'slug' => 'public-test-slug',
            'name' => 'Public Test Link',
            'is_active' => true,
            'created_by' => $this->admin->id,
        ]);

        // Access route as a guest (no actingAs!)
        $response = $this->get(route('public.dashboard.sub-accounts', [
            'slug' => $link->slug,
            'chargeable_account' => $account->id,
        ]));

        $response->assertStatus(200);
        $response->assertViewIs('public-sub-account-dashboard');
        $response->assertViewHasAll(['link', 'chargeableAccount', 'chartLabels', 'remainingBalances', 'subAccountData']);
        $response->assertSee('Public Sub Section');
        $response->assertSee('500.00');
        $response->assertSee('Public Test Link');
    }

    public function test_guest_cannot_access_public_sub_account_dashboard_with_inactive_or_missing_slug(): void
    {
        $account = ChargeableAccount::create([
            'name' => 'Public Project B',
            'classification' => 'Running',
            'status' => 'Active',
        ]);

        $inactiveLink = PublicDashboardLink::create([
            'slug' => 'inactive-slug',
            'name' => 'Inactive Test Link',
            'is_active' => false,
            'created_by' => $this->admin->id,
        ]);

        // Attempting with inactive slug
        $response = $this->get(route('public.dashboard.sub-accounts', [
            'slug' => $inactiveLink->slug,
            'chargeable_account' => $account->id,
        ]));
        $response->assertStatus(404);

        // Attempting with completely non-existent slug
        $response = $this->get(route('public.dashboard.sub-accounts', [
            'slug' => 'does-not-exist',
            'chargeable_account' => $account->id,
        ]));
        $response->assertStatus(404);
    }

    public function test_guest_cannot_access_public_sub_account_dashboard_for_inactive_account(): void
    {
        $inactiveAccount = ChargeableAccount::create([
            'name' => 'Inactive Public Project',
            'classification' => 'Running',
            'status' => 'Inactive', // Inactive account!
        ]);

        $link = PublicDashboardLink::create([
            'slug' => 'public-active-slug',
            'name' => 'Public Test Link',
            'is_active' => true,
            'created_by' => $this->admin->id,
        ]);

        $response = $this->get(route('public.dashboard.sub-accounts', [
            'slug' => $link->slug,
            'chargeable_account' => $inactiveAccount->id,
        ]));

        $response->assertStatus(404);
    }

    public function test_sub_account_show_page_displays_utilization_entries(): void
    {
        $account = ChargeableAccount::create([
            'name' => 'Main Account',
            'classification' => 'Running',
            'status' => 'Active',
        ]);

        $subAccount = SubAccount::create([
            'chargeable_account_id' => $account->id,
            'name' => 'Sub Active',
        ]);

        $order = FuelOrder::create([
            'asset_id' => $this->asset->id,
            'sub_account_id' => $subAccount->id,
            'status' => 'DONE',
            'actual_quantity' => 150,
        ]);

        $entry = UtilizationEntry::create([
            'asset_id' => $this->asset->id,
            'date' => '2026-08-15',
            'start_time' => '08:00',
            'end_time' => '12:00',
            'chargeable_account_id' => $account->id,
            'sub_account_id' => $subAccount->id,
            'fuel_order_id' => $order->id,
            'calculation_type' => 'Timeframe',
            'driver_operator_name' => 'John Operator',
            'reference' => 'REF-1234',
            'particulars' => 'Road Work',
        ]);

        $response = $this->actingAs($this->admin)->get(route('sub-accounts.show', $subAccount));

        $response->assertStatus(200);
        $response->assertDontSee('John Operator');
        $response->assertDontSee('REF-1234');
        $response->assertDontSee('Road Work');
    }

    public function test_fuel_orders_index_filters_by_chargeable_account_id_and_status(): void
    {
        $account1 = ChargeableAccount::create([
            'name' => 'Account 1',
            'classification' => 'Running',
            'status' => 'Active',
        ]);
        $account2 = ChargeableAccount::create([
            'name' => 'Account 2',
            'classification' => 'Running',
            'status' => 'Active',
        ]);

        $subAccount1 = SubAccount::create([
            'chargeable_account_id' => $account1->id,
            'name' => 'Sub 1',
        ]);
        $subAccount2 = SubAccount::create([
            'chargeable_account_id' => $account2->id,
            'name' => 'Sub 2',
        ]);

        // Direct order for account 1
        $order1 = FuelOrder::create([
            'status' => 'DONE',
            'actual_quantity' => 100,
            'chargeable_account_id' => $account1->id,
        ]);

        // Direct order for account 2
        $order2 = FuelOrder::create([
            'status' => 'DONE',
            'actual_quantity' => 200,
            'chargeable_account_id' => $account2->id,
        ]);

        // Nested order for account 1 via utilization entry
        $order3 = FuelOrder::create([
            'status' => 'DONE',
            'actual_quantity' => 300,
        ]);
        UtilizationEntry::create([
            'asset_id' => $this->asset->id,
            'date' => '2026-08-15',
            'start_time' => '08:00',
            'end_time' => '13:00',
            'chargeable_account_id' => $account1->id,
            'sub_account_id' => $subAccount1->id,
            'fuel_order_id' => $order3->id,
            'calculation_type' => 'Actual Hours',
            'actual_hours' => 5,
            'driver_operator_name' => 'John Operator',
            'reference' => 'REF-1234',
            'particulars' => 'Road Work',
        ]);

        // Assert dashboard renders the box arrow link
        $dashboardResponse = $this->actingAs($this->admin)->get(route('dashboard.sub-accounts', $account1));
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('bi-box-arrow-up-right');

        // Test filtering by chargeable_account_id
        $filterResponse = $this->actingAs($this->admin)->get(route('fuel-orders.index', [
            'chargeable_account_id' => $account1->id,
            'status' => 'DONE',
        ]));

        $filterResponse->assertStatus(200);
        // Should see order 1 and order 3
        $filterResponse->assertSee('#'.str_pad($order1->id, 5, '0', STR_PAD_LEFT));
        $filterResponse->assertSee('#'.str_pad($order3->id, 5, '0', STR_PAD_LEFT));
        // Should NOT see order 2
        $filterResponse->assertDontSee('#'.str_pad($order2->id, 5, '0', STR_PAD_LEFT));
    }
}
