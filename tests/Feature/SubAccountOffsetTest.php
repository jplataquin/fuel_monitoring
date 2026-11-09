<?php

namespace Tests\Feature;

use App\Models\ChargeableAccount;
use App\Models\SubAccount;
use App\Models\SubAccountBudget;
use App\Models\SubAccountOffset;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubAccountOffsetTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $budgeteer;

    protected User $moderator;

    protected ChargeableAccount $account;

    protected SubAccount $subAccount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([ValidateCsrfToken::class]);

        $this->admin = User::factory()->create(['role' => 'administrator']);
        $this->budgeteer = User::factory()->create(['role' => 'budgeteer']);
        $this->moderator = User::factory()->create(['role' => 'moderator']);

        $this->account = ChargeableAccount::create([
            'name' => 'Project Alpha',
            'classification' => 'Running',
            'status' => 'Active',
        ]);

        $this->subAccount = SubAccount::create([
            'chargeable_account_id' => $this->account->id,
            'name' => 'Earthworks',
        ]);

        SubAccountBudget::create([
            'sub_account_id' => $this->subAccount->id,
            'budget_quantity' => 500,
            'status' => 'Approved',
        ]);
    }

    public function test_budgeteer_can_submit_offset_with_pending_status(): void
    {
        $response = $this->actingAs($this->budgeteer)->post(route('sub-accounts.offsets.store', $this->subAccount), [
            'quantity' => 50,
            'remarks' => 'Additional allowance needed',
        ]);

        $response->assertRedirect(route('sub-accounts.show', $this->subAccount));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('sub_account_offsets', [
            'sub_account_id' => $this->subAccount->id,
            'quantity' => 50.0,
            'status' => 'Pending',
            'remarks' => 'Additional allowance needed',
            'created_by' => $this->budgeteer->id,
        ]);
    }

    public function test_budgeteer_can_submit_negative_offset(): void
    {
        $response = $this->actingAs($this->budgeteer)->post(route('sub-accounts.offsets.store', $this->subAccount), [
            'quantity' => -30,
            'remarks' => 'Deduction for equipment reallocation',
        ]);

        $response->assertRedirect(route('sub-accounts.show', $this->subAccount));

        $this->assertDatabaseHas('sub_account_offsets', [
            'sub_account_id' => $this->subAccount->id,
            'quantity' => -30.0,
            'status' => 'Pending',
            'remarks' => 'Deduction for equipment reallocation',
        ]);
    }

    public function test_admin_can_approve_offset_and_it_updates_remaining_budget(): void
    {
        $offset = SubAccountOffset::create([
            'sub_account_id' => $this->subAccount->id,
            'quantity' => 100,
            'status' => 'Pending',
            'created_by' => $this->budgeteer->id,
        ]);

        $this->assertEquals(500.0, $this->subAccount->remainingBudget());

        $response = $this->actingAs($this->admin)->patch(route('sub-account-offsets.approve', $offset));

        $response->assertRedirect(route('sub-accounts.show', $this->subAccount));

        $offset->refresh();
        $this->assertEquals('Approved', $offset->status);
        $this->assertEquals($this->admin->id, $offset->approved_by);
        $this->assertNotNull($offset->approved_at);

        // Budget was 500, offset is +100 => total remaining budget should now be 600
        $this->assertEquals(600.0, $this->subAccount->fresh()->remainingBudget());
    }

    public function test_admin_can_reject_offset(): void
    {
        $offset = SubAccountOffset::create([
            'sub_account_id' => $this->subAccount->id,
            'quantity' => 100,
            'status' => 'Pending',
            'created_by' => $this->budgeteer->id,
        ]);

        $response = $this->actingAs($this->admin)->patch(route('sub-account-offsets.reject', $offset));

        $response->assertRedirect(route('sub-accounts.show', $this->subAccount));

        $offset->refresh();
        $this->assertEquals('Rejected', $offset->status);
        $this->assertEquals($this->admin->id, $offset->rejected_by);
        $this->assertNotNull($offset->rejected_at);

        // Rejected offset should NOT affect remaining budget
        $this->assertEquals(500.0, $this->subAccount->fresh()->remainingBudget());
    }

    public function test_user_can_delete_pending_offset(): void
    {
        $offset = SubAccountOffset::create([
            'sub_account_id' => $this->subAccount->id,
            'quantity' => 100,
            'status' => 'Pending',
            'created_by' => $this->budgeteer->id,
        ]);

        $response = $this->actingAs($this->budgeteer)->delete(route('sub-account-offsets.destroy', $offset));

        $response->assertRedirect(route('sub-accounts.show', $this->subAccount));

        $this->assertSoftDeleted('sub_account_offsets', [
            'id' => $offset->id,
            'deleted_by' => $this->budgeteer->id,
        ]);
    }

    public function test_dashboard_reflects_approved_offsets_in_total_budget(): void
    {
        // Approve an offset of -50
        SubAccountOffset::create([
            'sub_account_id' => $this->subAccount->id,
            'quantity' => -50,
            'status' => 'Approved',
            'approved_by' => $this->admin->id,
            'approved_at' => now(),
        ]);

        // Dashboard sub-accounts page: Total Budget should be 500 + (-50) = 450
        $response = $this->actingAs($this->admin)->get(route('dashboard.sub-accounts', $this->account));
        $response->assertStatus(200);

        $subAccountData = $response->viewData('subAccountData');
        $this->assertEquals(500.0, $subAccountData[0]['budget']);
        $this->assertEquals(-50.0, $subAccountData[0]['offset']);
        $this->assertEquals(450.0, $subAccountData[0]['total_budget']);
    }

    public function test_sub_account_breakdown_displays_offset_column_and_formatted_values(): void
    {
        $subAccount2 = SubAccount::create([
            'chargeable_account_id' => $this->account->id,
            'name' => 'Grading',
        ]);

        SubAccountBudget::create([
            'sub_account_id' => $subAccount2->id,
            'budget_quantity' => 300,
            'status' => 'Approved',
        ]);

        // Negative offset for subAccount 1 (-50)
        SubAccountOffset::create([
            'sub_account_id' => $this->subAccount->id,
            'quantity' => -50,
            'status' => 'Approved',
            'approved_by' => $this->admin->id,
            'approved_at' => now(),
        ]);

        // Positive offset for subAccount 2 (+75)
        SubAccountOffset::create([
            'sub_account_id' => $subAccount2->id,
            'quantity' => 75,
            'status' => 'Approved',
            'approved_by' => $this->admin->id,
            'approved_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('dashboard.sub-accounts', $this->account));
        $response->assertStatus(200);

        // Headers exist
        $response->assertSee('Budget (L)');
        $response->assertSee('Offset (L)');
        $response->assertSee('Total Budget (L)');

        // Formatted budget, offset, and total budget values
        $response->assertSee('500.00');
        $response->assertSee('-50.00');
        $response->assertSee('450.00');
        $response->assertSee('300.00');
        $response->assertSee('+75.00');
        $response->assertSee('375.00');
    }
}
