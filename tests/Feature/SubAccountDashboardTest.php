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

        $entry = UtilizationEntry::create([
            'asset_id' => $this->asset->id,
            'date' => '2026-08-15',
            'start_time' => '08:00',
            'end_time' => '12:00',
            'chargeable_account_id' => $account->id,
            'sub_account_id' => $subAccount->id,
            'calculation_type' => 'Timeframe',
            'driver_operator_name' => 'John Operator',
            'reference' => 'REF-1234',
            'particulars' => 'Road Work',
        ]);

        $response = $this->actingAs($this->admin)->get(route('sub-accounts.show', $subAccount));

        $response->assertStatus(200);
        $response->assertSee('John Operator');
        $response->assertSee('REF-1234');
        $response->assertSee('Road Work');
        $response->assertSee('EX-900');
    }
}
