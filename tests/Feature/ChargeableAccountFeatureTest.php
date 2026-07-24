<?php

namespace Tests\Feature;

use App\Models\ChargeableAccount;
use App\Models\ChargeableAccountOffset;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChargeableAccountFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_access_chargeable_accounts_routes()
    {
        $user = User::factory()->create(['role' => 'administrator']);

        $response = $this->actingAs($user)->get(route('chargeable-accounts.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get(route('chargeable-accounts.create'));
        $response->assertStatus(200);
    }

    public function test_chargeable_accounts_index_displays_sub_account_count()
    {
        $user = User::factory()->create(['role' => 'administrator']);
        $account = ChargeableAccount::create([
            'name' => 'Project Gamma',
            'classification' => 'Running',
            'status' => 'Active',
        ]);

        $account->subAccounts()->create(['name' => 'Sub Account 1']);
        $account->subAccounts()->create(['name' => 'Sub Account 2']);

        $response = $this->actingAs($user)->get(route('chargeable-accounts.index'));
        $response->assertStatus(200);
        $response->assertSee('Sub-Account');
        $response->assertSee('2');
    }

    public function test_chargeable_accounts_index_has_alpine_sorting_searching_and_toggle()
    {
        $user = User::factory()->create(['role' => 'administrator']);

        $activeAccount = ChargeableAccount::create([
            'name' => 'Active Project Alpha',
            'classification' => 'Running',
            'status' => 'Active',
        ]);

        $inactiveAccount = ChargeableAccount::create([
            'name' => 'Inactive Project Beta',
            'classification' => 'Running',
            'status' => 'Inactive',
        ]);

        $response = $this->actingAs($user)->get(route('chargeable-accounts.index'));
        $response->assertStatus(200);

        $response->assertSee('x-model="search"', false);
        $response->assertSee('x-model="showInactive"', false);

        $response->assertSee("@click=\"sort('account')\"", false);
        $response->assertSee("@click=\"sort('type')\"", false);
        $response->assertSee("@click=\"sort('sub-account')\"", false);
        $response->assertSee("@click=\"sort('status')\"", false);

        $response->assertSee('data-account="active project alpha"', false);
        $response->assertSee('data-account="inactive project beta"', false);
        $response->assertSee('data-status="active"', false);
        $response->assertSee('data-status="inactive"', false);
    }

    public function test_standard_user_cannot_access_chargeable_accounts_routes()
    {
        $user = User::factory()->create(['role' => 'data_logger']);

        $response = $this->actingAs($user)->get(route('chargeable-accounts.index'));
        $response->assertStatus(403);
    }

    public function test_administrator_can_create_chargeable_account()
    {
        $this->withoutMiddleware([ValidateCsrfToken::class]);
        $user = User::factory()->create(['role' => 'administrator']);

        // Test creating Running account
        $response = $this->actingAs($user)->post(route('chargeable-accounts.store'), [
            'name' => 'Project Alpha',
            'classification' => 'Running',
            'status' => 'Active',
        ]);

        $response->assertRedirect(route('chargeable-accounts.index'));
        $this->assertDatabaseHas('chargeable_accounts', [
            'name' => 'Project Alpha',
            'classification' => 'Running',
            'status' => 'Active',
        ]);

        // Test creating Scoped account
        $response = $this->actingAs($user)->post(route('chargeable-accounts.store'), [
            'name' => 'Project Beta',
            'classification' => 'Scoped',
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addMonth()->format('Y-m-d'),
            'status' => 'Active',
        ]);

        $response->assertRedirect(route('chargeable-accounts.index'));
        $this->assertDatabaseHas('chargeable_accounts', [
            'name' => 'Project Beta',
            'classification' => 'Scoped',
            'start_date' => now()->format('Y-m-d').' 00:00:00',
            'end_date' => now()->addMonth()->format('Y-m-d').' 00:00:00',
        ]);
    }

    public function test_scoped_account_requires_dates()
    {
        $this->withoutMiddleware([ValidateCsrfToken::class]);
        $user = User::factory()->create(['role' => 'administrator']);

        $response = $this->actingAs($user)->post(route('chargeable-accounts.store'), [
            'name' => 'Project Gamma',
            'classification' => 'Scoped',
            'status' => 'Active',
        ]);

        $response->assertSessionHasErrors(['start_date', 'end_date']);
    }

    public function test_administrator_can_update_chargeable_account()
    {
        $this->withoutMiddleware([ValidateCsrfToken::class]);
        $user = User::factory()->create(['role' => 'administrator']);
        $account = ChargeableAccount::create(['name' => 'Old Name', 'classification' => 'Running', 'status' => 'Active']);

        $response = $this->actingAs($user)->patch(route('chargeable-accounts.update', $account), [
            'name' => 'New Name',
            'classification' => 'Scoped',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'status' => 'Inactive',
        ]);

        $response->assertRedirect(route('chargeable-accounts.index'));
        $this->assertDatabaseHas('chargeable_accounts', [
            'id' => $account->id,
            'name' => 'New Name',
            'classification' => 'Scoped',
            'start_date' => '2026-01-01 00:00:00',
            'end_date' => '2026-12-31 00:00:00',
            'status' => 'Inactive',
        ]);
    }

    public function test_chargeable_account_must_have_unique_name()
    {
        $this->withoutMiddleware([ValidateCsrfToken::class]);
        $user = User::factory()->create(['role' => 'administrator']);
        ChargeableAccount::create(['name' => 'Existing Account', 'status' => 'Active']);

        $response = $this->actingAs($user)->post(route('chargeable-accounts.store'), [
            'name' => 'Existing Account',
            'status' => 'Active',
        ]);

        $response->assertSessionHasErrors('name');
        $this->assertEquals(1, ChargeableAccount::count());
    }

    public function test_administrator_can_soft_delete_chargeable_account()
    {
        $this->withoutMiddleware([ValidateCsrfToken::class]);
        $user = User::factory()->create(['role' => 'administrator']);
        $account = ChargeableAccount::create(['name' => 'To Be Deleted']);

        $response = $this->actingAs($user)->delete(route('chargeable-accounts.destroy', $account));

        $response->assertRedirect(route('chargeable-accounts.index'));
        $this->assertSoftDeleted('chargeable_accounts', [
            'id' => $account->id,
        ]);
    }

    public function test_administrator_can_add_and_delete_offsets(): void
    {
        $this->withoutMiddleware([ValidateCsrfToken::class]);
        $admin = User::factory()->create(['role' => 'administrator']);
        $account = ChargeableAccount::create(['name' => 'Offset Account', 'status' => 'Active']);

        // Test storing offset
        $response = $this->actingAs($admin)->post("/chargeable-accounts/{$account->id}/offsets", [
            'quantity' => 150.50,
            'remarks' => 'Pre-system fuel',
        ]);

        $response->assertRedirect("/chargeable-accounts/{$account->id}");
        $this->assertDatabaseHas('chargeable_account_offsets', [
            'chargeable_account_id' => $account->id,
            'quantity' => 150.50,
            'remarks' => 'Pre-system fuel',
            'created_by' => $admin->id,
        ]);

        $offset = ChargeableAccountOffset::first();

        // Test updating offset
        $updateResponse = $this->actingAs($admin)->patch("/chargeable-accounts/{$account->id}/offsets/{$offset->id}", [
            'quantity' => 200.00,
            'remarks' => 'Updated remarks',
        ]);

        $updateResponse->assertRedirect("/chargeable-accounts/{$account->id}");
        $this->assertDatabaseHas('chargeable_account_offsets', [
            'id' => $offset->id,
            'quantity' => 200.00,
            'remarks' => 'Updated remarks',
        ]);

        // Test deleting offset
        $deleteResponse = $this->actingAs($admin)->delete("/chargeable-accounts/{$account->id}/offsets/{$offset->id}");

        $deleteResponse->assertRedirect("/chargeable-accounts/{$account->id}");
        $this->assertDatabaseMissing('chargeable_account_offsets', [
            'id' => $offset->id,
        ]);
    }

    public function test_non_administrator_cannot_manage_offsets(): void
    {
        $this->withoutMiddleware([ValidateCsrfToken::class]);
        $user = User::factory()->create(['role' => 'data_logger']);
        $account = ChargeableAccount::create(['name' => 'Another Account', 'status' => 'Active']);

        $response = $this->actingAs($user)->post("/chargeable-accounts/{$account->id}/offsets", [
            'quantity' => 150.50,
        ]);

        $response->assertForbidden();
    }
}
