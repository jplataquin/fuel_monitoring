<?php

namespace Tests\Feature;

use App\Models\ChargeableAccount;
use App\Models\SubAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_add_sub_account(): void
    {
        $this->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);
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
        $this->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);
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
        $this->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);
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
        $this->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);
        $response = $this->actingAs($user)->post(route('chargeable-accounts.sub-accounts.store', $account1), [
            'name' => 'Shared Name',
        ]);
        $response->assertSessionHasErrors('name');

        // Different parent account should pass
        $this->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);
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
        $this->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);
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
        $this->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);
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
        $this->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);
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
        $this->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);
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
        $this->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);
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
        $this->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);
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
        $this->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);
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
}
