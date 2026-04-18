<?php

namespace Tests\Feature;

use App\Models\ChargeableAccount;
use App\Models\SubAccount;
use App\Models\SubAccountBudget;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgeteerAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);
    }

    public function test_budgeteer_can_manage_chargeable_accounts(): void
    {
        $user = User::factory()->create(['role' => 'budgeteer']);

        // Create
        $response = $this->actingAs($user)->post(route('chargeable-accounts.store'), [
            'name' => 'New Account',
            'status' => 'Active',
        ]);
        $response->assertRedirect(route('chargeable-accounts.index'));
        $this->assertDatabaseHas('chargeable_accounts', ['name' => 'New Account']);

        $account = ChargeableAccount::where('name', 'New Account')->first();

        // Edit
        $response = $this->actingAs($user)->patch(route('chargeable-accounts.update', $account), [
            'name' => 'Updated Account',
            'status' => 'Active',
        ]);
        $response->assertRedirect(route('chargeable-accounts.index'));
        $this->assertDatabaseHas('chargeable_accounts', ['name' => 'Updated Account']);

        // Delete
        $response = $this->actingAs($user)->delete(route('chargeable-accounts.destroy', $account));
        $response->assertRedirect(route('chargeable-accounts.index'));
        $this->assertSoftDeleted('chargeable_accounts', ['id' => $account->id]);
    }

    public function test_budgeteer_can_manage_sub_accounts(): void
    {
        $user = User::factory()->create(['role' => 'budgeteer']);
        $account = ChargeableAccount::create(['name' => 'Main Account', 'status' => 'Active']);

        // Create
        $response = $this->actingAs($user)->post(route('chargeable-accounts.sub-accounts.store', $account), [
            'name' => 'New Sub Account',
        ]);
        $response->assertRedirect(route('chargeable-accounts.show', $account));
        $this->assertDatabaseHas('sub_accounts', ['name' => 'New Sub Account']);

        $subAccount = SubAccount::where('name', 'New Sub Account')->first();

        // Edit
        $response = $this->actingAs($user)->patch(route('sub-accounts.update', $subAccount), [
            'name' => 'Updated Sub Account',
        ]);
        $response->assertRedirect(route('chargeable-accounts.show', $account));
        $this->assertDatabaseHas('sub_accounts', ['name' => 'Updated Sub Account']);

        // Delete
        $response = $this->actingAs($user)->delete(route('sub-accounts.destroy', $subAccount));
        $response->assertRedirect(route('chargeable-accounts.show', $account));
        $this->assertSoftDeleted('sub_accounts', ['id' => $subAccount->id]);
    }

    public function test_budgeteer_can_edit_pending_budget(): void
    {
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
            'remarks' => 'Updated remarks',
        ]);

        $response->assertRedirect(route('account-budgets.index'));
        $this->assertDatabaseHas('sub_account_budgets', [
            'id' => $budget->id,
            'budget_quantity' => 200,
            'remarks' => 'Updated remarks',
        ]);
    }

    public function test_budgeteer_cannot_edit_approved_budget(): void
    {
        $user = User::factory()->create(['role' => 'budgeteer']);
        $account = ChargeableAccount::create(['name' => 'Main Account', 'status' => 'Active']);
        $subAccount = $account->subAccounts()->create(['name' => 'Sub Account']);
        $budget = $subAccount->budgets()->create([
            'budget_quantity' => 100,
            'status' => 'Approved',
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->patch(route('account-budgets.update', $budget), [
            'sub_account_id' => $subAccount->id,
            'budget_quantity' => 200,
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('sub_account_budgets', [
            'id' => $budget->id,
            'budget_quantity' => 100,
        ]);
    }

    public function test_budgeteer_cannot_delete_approved_budget(): void
    {
        $user = User::factory()->create(['role' => 'budgeteer']);
        $account = ChargeableAccount::create(['name' => 'Main Account', 'status' => 'Active']);
        $subAccount = $account->subAccounts()->create(['name' => 'Sub Account']);
        $budget = $subAccount->budgets()->create([
            'budget_quantity' => 100,
            'status' => 'Approved',
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->delete(route('account-budgets.destroy', $budget));

        $response->assertStatus(403);
        $this->assertDatabaseHas('sub_account_budgets', [
            'id' => $budget->id,
            'deleted_at' => null,
        ]);
    }

    public function test_budgeteer_cannot_edit_rejected_budget(): void
    {
        $user = User::factory()->create(['role' => 'budgeteer']);
        $account = ChargeableAccount::create(['name' => 'Main Account', 'status' => 'Active']);
        $subAccount = $account->subAccounts()->create(['name' => 'Sub Account']);
        $budget = $subAccount->budgets()->create([
            'budget_quantity' => 100,
            'status' => 'Rejected',
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->patch(route('account-budgets.update', $budget), [
            'sub_account_id' => $subAccount->id,
            'budget_quantity' => 200,
        ]);

        $response->assertStatus(403);
    }
}
