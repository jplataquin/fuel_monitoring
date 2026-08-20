<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetType;
use App\Models\ChargeableAccount;
use App\Models\FuelOrder;
use App\Models\SubAccount;
use App\Models\SubAccountBudget;
use App\Models\User;
use App\Models\UtilizationEntry;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_add_sub_account(): void
    {
        $this->withoutMiddleware([ValidateCsrfToken::class]);
        $user = User::factory()->create(['role' => 'administrator']);
        $account = ChargeableAccount::create(['name' => 'Main Account', 'status' => 'Active']);

        $response = $this->actingAs($user)->post(route('chargeable-accounts.sub-accounts.store', $account), [
            'name' => 'Sub Account 1',
        ]);

        $response->assertRedirect(route('chargeable-accounts.show', $account));
        $this->assertDatabaseHas('sub_accounts', [
            'chargeable_account_id' => $account->id,
            'name' => 'Sub Account 1',
        ]);
    }

    public function test_moderator_can_add_sub_account(): void
    {
        $this->withoutMiddleware([ValidateCsrfToken::class]);
        $user = User::factory()->create(['role' => 'moderator']);
        $account = ChargeableAccount::create(['name' => 'Main Account', 'status' => 'Active']);

        $response = $this->actingAs($user)->post(route('chargeable-accounts.sub-accounts.store', $account), [
            'name' => 'Sub Account 1',
        ]);

        $response->assertRedirect(route('chargeable-accounts.show', $account));
        $this->assertDatabaseHas('sub_accounts', [
            'chargeable_account_id' => $account->id,
            'name' => 'Sub Account 1',
        ]);
    }

    public function test_data_logger_cannot_add_sub_account(): void
    {
        $this->withoutMiddleware([ValidateCsrfToken::class]);
        $user = User::factory()->create(['role' => 'data_logger']);
        $account = ChargeableAccount::create(['name' => 'Main Account', 'status' => 'Active']);

        $response = $this->actingAs($user)->post(route('chargeable-accounts.sub-accounts.store', $account), [
            'name' => 'Sub Account 1',
        ]);

        $response->assertStatus(403);
    }

    public function test_sub_account_name_must_be_unique_within_parent_account(): void
    {
        $user = User::factory()->create(['role' => 'administrator']);
        $account1 = ChargeableAccount::create(['name' => 'Account 1', 'status' => 'Active']);
        $account2 = ChargeableAccount::create(['name' => 'Account 2', 'status' => 'Active']);

        SubAccount::create([
            'chargeable_account_id' => $account1->id,
            'name' => 'Shared Name',
        ]);

        // Same parent account should fail
        $this->withoutMiddleware([ValidateCsrfToken::class]);
        $response = $this->actingAs($user)->post(route('chargeable-accounts.sub-accounts.store', $account1), [
            'name' => 'Shared Name',
        ]);
        $response->assertSessionHasErrors('name');

        // Different parent account should pass
        $this->withoutMiddleware([ValidateCsrfToken::class]);
        $response = $this->actingAs($user)->post(route('chargeable-accounts.sub-accounts.store', $account2), [
            'name' => 'Shared Name',
        ]);
        $response->assertRedirect(route('chargeable-accounts.show', $account2));
        $this->assertDatabaseHas('sub_accounts', [
            'chargeable_account_id' => $account2->id,
            'name' => 'Shared Name',
        ]);
    }

    public function test_administrator_can_delete_sub_account(): void
    {
        $this->withoutMiddleware([ValidateCsrfToken::class]);
        $user = User::factory()->create(['role' => 'administrator']);
        $account = ChargeableAccount::create(['name' => 'Main Account', 'status' => 'Active']);
        $subAccount = $account->subAccounts()->create(['name' => 'To Delete']);

        $response = $this->actingAs($user)->delete(route('sub-accounts.destroy', $subAccount));

        $response->assertRedirect(route('chargeable-accounts.show', $account));
        $this->assertSoftDeleted('sub_accounts', ['id' => $subAccount->id]);
    }

    public function test_authorized_user_can_view_sub_account_details(): void
    {
        $user = User::factory()->create(['role' => 'administrator']);
        $account = ChargeableAccount::create(['name' => 'Main Account', 'status' => 'Active']);
        $subAccount = $account->subAccounts()->create(['name' => 'Specific Sub']);

        $response = $this->actingAs($user)->get(route('sub-accounts.show', $subAccount));

        $response->assertStatus(200);
        $response->assertSee('Specific Sub');
        $response->assertSee('Main Account');
    }

    public function test_sub_account_name_can_be_reused_after_soft_delete(): void
    {
        $this->withoutMiddleware([ValidateCsrfToken::class]);
        $user = User::factory()->create(['role' => 'administrator']);
        $account = ChargeableAccount::create(['name' => 'Main Account', 'status' => 'Active']);

        $sub1 = $account->subAccounts()->create(['name' => 'Reusable Name']);
        $sub1->delete();

        // Should pass now because the first one is soft-deleted
        $response = $this->actingAs($user)->post(route('chargeable-accounts.sub-accounts.store', $account), [
            'name' => 'Reusable Name',
        ]);

        $response->assertRedirect(route('chargeable-accounts.show', $account));
        $this->assertDatabaseHas('sub_accounts', [
            'chargeable_account_id' => $account->id,
            'name' => 'Reusable Name',
            'deleted_at' => null,
        ]);

        // Both should exist in DB (one soft-deleted, one active)
        $this->assertEquals(2, SubAccount::withTrashed()->where('name', 'Reusable Name')->count());
    }

    public function test_administrator_can_allocate_budget_from_sub_account_page(): void
    {
        $this->withoutMiddleware([ValidateCsrfToken::class]);
        $user = User::factory()->create(['role' => 'administrator']);
        $account = ChargeableAccount::create(['name' => 'Main Account', 'status' => 'Active']);
        $subAccount = $account->subAccounts()->create(['name' => 'Sub Account']);

        $response = $this->actingAs($user)->post(route('account-budgets.store'), [
            'sub_account_id' => $subAccount->id,
            'budget_quantity' => 500,
            'remarks' => 'Test budget allocation',
        ]);

        $response->assertRedirect(route('sub-accounts.show', $subAccount));
        $this->assertDatabaseHas('sub_account_budgets', [
            'sub_account_id' => $subAccount->id,
            'budget_quantity' => 500,
            'status' => 'Pending',
        ]);
    }

    public function test_budgeteer_can_allocate_budget_from_sub_account_page(): void
    {
        $this->withoutMiddleware([ValidateCsrfToken::class]);
        $user = User::factory()->create(['role' => 'budgeteer']);
        $account = ChargeableAccount::create(['name' => 'Main Account', 'status' => 'Active']);
        $subAccount = $account->subAccounts()->create(['name' => 'Sub Account']);

        $response = $this->actingAs($user)->post(route('account-budgets.store'), [
            'sub_account_id' => $subAccount->id,
            'budget_quantity' => 750.50,
        ]);

        $response->assertRedirect(route('sub-accounts.show', $subAccount));
        $this->assertDatabaseHas('sub_account_budgets', [
            'sub_account_id' => $subAccount->id,
            'budget_quantity' => 750.50,
        ]);
    }

    public function test_moderator_can_allocate_budget(): void
    {
        $this->withoutMiddleware([ValidateCsrfToken::class]);
        $user = User::factory()->create(['role' => 'moderator']);
        $account = ChargeableAccount::create(['name' => 'Main Account', 'status' => 'Active']);
        $subAccount = $account->subAccounts()->create(['name' => 'Sub Account']);

        $response = $this->actingAs($user)->post(route('account-budgets.store'), [
            'sub_account_id' => $subAccount->id,
            'budget_quantity' => 100,
        ]);

        $response->assertRedirect(route('sub-accounts.show', $subAccount));
        $this->assertDatabaseHas('sub_account_budgets', [
            'sub_account_id' => $subAccount->id,
            'budget_quantity' => 100,
        ]);
    }

    public function test_budgeteer_cannot_update_budget_status(): void
    {
        $this->withoutMiddleware([ValidateCsrfToken::class]);
        $user = User::factory()->create(['role' => 'budgeteer']);
        $account = ChargeableAccount::create(['name' => 'Main Account', 'status' => 'Active']);
        $subAccount = $account->subAccounts()->create(['name' => 'Sub Account']);
        $budget = $subAccount->budgets()->create([
            'budget_quantity' => 100,
            'status' => 'Pending',
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->patch(route('account-budgets.update', $budget), [
            'sub_account_id' => $subAccount->id,
            'budget_quantity' => 200,
            'status' => 'Approved', // Attempting to approve
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('sub_account_budgets', [
            'id' => $budget->id,
            'budget_quantity' => 200,
            'status' => 'Pending', // Status should REMAIN Pending
        ]);
    }

    public function test_moderator_can_update_budget_status(): void
    {
        $this->withoutMiddleware([ValidateCsrfToken::class]);
        $user = User::factory()->create(['role' => 'moderator']);
        $account = ChargeableAccount::create(['name' => 'Main Account', 'status' => 'Active']);
        $subAccount = $account->subAccounts()->create(['name' => 'Sub Account']);
        $budget = $subAccount->budgets()->create([
            'budget_quantity' => 100,
            'status' => 'Pending',
        ]);

        $response = $this->actingAs($user)->patch(route('account-budgets.update', $budget), [
            'sub_account_id' => $subAccount->id,
            'budget_quantity' => 100,
            'status' => 'Approved',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('sub_account_budgets', [
            'id' => $budget->id,
            'status' => 'Approved',
        ]);
    }

    public function test_administrator_can_merge_sub_accounts_and_retroactively_affects_records(): void
    {
        $this->withoutMiddleware([ValidateCsrfToken::class]);
        $admin = User::factory()->create(['role' => 'administrator']);

        $account = ChargeableAccount::create(['name' => 'Main Account', 'status' => 'Active']);
        $subAccountA = $account->subAccounts()->create(['name' => 'Sub Account A']);
        $subAccountB = $account->subAccounts()->create(['name' => 'Sub Account B']);

        $assetType = AssetType::create(['name' => 'Truck']);
        $asset = Asset::create([
            'fleet_no' => 'TRK-01',
            'asset_type_id' => $assetType->id,
            'fuel_factor_km' => 1.5,
            'fuel_factor_hr' => 0,
            'tank_capacity' => 100,
        ]);

        $budget = SubAccountBudget::create([
            'sub_account_id' => $subAccountA->id,
            'budget_quantity' => 1000,
            'status' => 'Approved',
        ]);

        $order = FuelOrder::create([
            'asset_id' => $asset->id,
            'sub_account_id' => $subAccountA->id,
            'status' => 'DONE',
            'actual_quantity' => 150,
        ]);

        $entry = UtilizationEntry::create([
            'asset_id' => $asset->id,
            'date' => '2026-08-15',
            'start_time' => '08:00',
            'end_time' => '12:00',
            'chargeable_account_id' => $account->id,
            'sub_account_id' => $subAccountA->id,
            'fuel_order_id' => $order->id,
            'calculation_type' => 'Timeframe',
            'driver_operator_name' => 'Operator',
            'reference' => 'REF-1',
            'particulars' => 'Mining',
        ]);

        $response = $this->actingAs($admin)->post(route('sub-accounts.merge', $subAccountA), [
            'merged_to_id' => $subAccountB->id,
            'merge_remarks' => 'Merging A into B',
        ]);

        $response->assertRedirect(route('chargeable-accounts.show', $account));
        $response->assertSessionHas('status');

        $this->assertSoftDeleted('sub_accounts', ['id' => $subAccountA->id]);

        $updatedSubAccountA = SubAccount::withTrashed()->find($subAccountA->id);
        $this->assertEquals($subAccountB->id, $updatedSubAccountA->merged_to_id);
        $this->assertEquals($admin->id, $updatedSubAccountA->merged_by);
        $this->assertNotNull($updatedSubAccountA->merged_at);
        $this->assertEquals('Merging A into B', $updatedSubAccountA->merge_remarks);

        // Assert related records are reassigned
        $this->assertDatabaseHas('sub_account_budgets', [
            'id' => $budget->id,
            'sub_account_id' => $subAccountB->id,
        ]);

        $this->assertDatabaseHas('fuel_orders', [
            'id' => $order->id,
            'sub_account_id' => $subAccountB->id,
        ]);

        $this->assertDatabaseHas('utilization_entries', [
            'id' => $entry->id,
            'sub_account_id' => $subAccountB->id,
        ]);
    }

    public function test_non_administrator_cannot_merge_sub_accounts(): void
    {
        $this->withoutMiddleware([ValidateCsrfToken::class]);
        $moderator = User::factory()->create(['role' => 'moderator']);

        $account = ChargeableAccount::create(['name' => 'Main Account', 'status' => 'Active']);
        $subAccountA = $account->subAccounts()->create(['name' => 'Sub Account A']);
        $subAccountB = $account->subAccounts()->create(['name' => 'Sub Account B']);

        $response = $this->actingAs($moderator)->post(route('sub-accounts.merge', $subAccountA), [
            'merged_to_id' => $subAccountB->id,
            'merge_remarks' => 'Illegal Merge',
        ]);

        $response->assertStatus(403);
    }

    public function test_merge_validation_fails_if_target_sub_account_from_another_chargeable_account(): void
    {
        $this->withoutMiddleware([ValidateCsrfToken::class]);
        $admin = User::factory()->create(['role' => 'administrator']);

        $account1 = ChargeableAccount::create(['name' => 'Account 1', 'status' => 'Active']);
        $account2 = ChargeableAccount::create(['name' => 'Account 2', 'status' => 'Active']);

        $subAccountA = $account1->subAccounts()->create(['name' => 'Sub Account A']);
        $subAccountB = $account2->subAccounts()->create(['name' => 'Sub Account B']);

        $response = $this->actingAs($admin)->post(route('sub-accounts.merge', $subAccountA), [
            'merged_to_id' => $subAccountB->id,
            'merge_remarks' => 'Cross-Account Merge',
        ]);

        $response->assertSessionHasErrors('merged_to_id');
    }

    public function test_merge_validation_fails_if_target_sub_account_is_same(): void
    {
        $this->withoutMiddleware([ValidateCsrfToken::class]);
        $admin = User::factory()->create(['role' => 'administrator']);

        $account = ChargeableAccount::create(['name' => 'Main Account', 'status' => 'Active']);
        $subAccountA = $account->subAccounts()->create(['name' => 'Sub Account A']);

        $response = $this->actingAs($admin)->post(route('sub-accounts.merge', $subAccountA), [
            'merged_to_id' => $subAccountA->id,
            'merge_remarks' => 'Self Merge',
        ]);

        $response->assertSessionHasErrors('merged_to_id');
    }

    public function test_authorized_user_can_update_sub_account_accomplishment(): void
    {
        $this->withoutMiddleware([ValidateCsrfToken::class]);
        $admin = User::factory()->create(['role' => 'administrator']);

        $account = ChargeableAccount::create(['name' => 'Main Account', 'status' => 'Active']);
        $subAccount = $account->subAccounts()->create(['name' => 'Sub Account A', 'accomplishment' => 10.5]);

        $response = $this->actingAs($admin)->patch(route('sub-accounts.update', $subAccount), [
            'name' => 'Updated Sub Account Name',
            'accomplishment' => 85.5,
        ]);

        $response->assertRedirect(route('chargeable-accounts.show', $account));
        $this->assertDatabaseHas('sub_accounts', [
            'id' => $subAccount->id,
            'name' => 'Updated Sub Account Name',
            'accomplishment' => 85.5,
        ]);
    }

    public function test_sub_account_accomplishment_validation_fails_beyond_100(): void
    {
        $this->withoutMiddleware([ValidateCsrfToken::class]);
        $admin = User::factory()->create(['role' => 'administrator']);

        $account = ChargeableAccount::create(['name' => 'Main Account', 'status' => 'Active']);
        $subAccount = $account->subAccounts()->create(['name' => 'Sub Account A']);

        $response = $this->actingAs($admin)->patch(route('sub-accounts.update', $subAccount), [
            'name' => 'Sub Account A',
            'accomplishment' => 100.1,
        ]);

        $response->assertSessionHasErrors('accomplishment');
    }

    public function test_sub_account_accomplishment_validation_fails_below_0(): void
    {
        $this->withoutMiddleware([ValidateCsrfToken::class]);
        $admin = User::factory()->create(['role' => 'administrator']);

        $account = ChargeableAccount::create(['name' => 'Main Account', 'status' => 'Active']);
        $subAccount = $account->subAccounts()->create(['name' => 'Sub Account A']);

        $response = $this->actingAs($admin)->patch(route('sub-accounts.update', $subAccount), [
            'name' => 'Sub Account A',
            'accomplishment' => -0.5,
        ]);

        $response->assertSessionHasErrors('accomplishment');
    }
}
