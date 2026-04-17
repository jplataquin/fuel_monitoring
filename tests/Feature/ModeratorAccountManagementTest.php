<?php

namespace Tests\Feature;

use App\Models\ChargeableAccount;
use App\Models\SubAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModeratorAccountManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_moderator_can_access_chargeable_accounts_index()
    {
        $user = User::factory()->create(['role' => 'moderator']);
        $response = $this->actingAs($user)->get(route('chargeable-accounts.index'));
        $response->assertStatus(200);
    }

    public function test_moderator_can_create_chargeable_account()
    {
        $this->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);
        $user = User::factory()->create(['role' => 'moderator']);

        $response = $this->actingAs($user)->post(route('chargeable-accounts.store'), [
            'name' => 'Moderator Account',
            'status' => 'Active',
        ]);

        $response->assertRedirect(route('chargeable-accounts.index'));
        $this->assertDatabaseHas('chargeable_accounts', [
            'name' => 'Moderator Account',
            'status' => 'Active',
        ]);
    }

    public function test_moderator_can_update_chargeable_account()
    {
        $this->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);
        $user = User::factory()->create(['role' => 'moderator']);
        $account = ChargeableAccount::create(['name' => 'Old Name', 'status' => 'Active']);

        $response = $this->actingAs($user)->patch(route('chargeable-accounts.update', $account), [
            'name' => 'Updated Name',
            'status' => 'Inactive',
        ]);

        $response->assertRedirect(route('chargeable-accounts.index'));
        $this->assertDatabaseHas('chargeable_accounts', [
            'id' => $account->id,
            'name' => 'Updated Name',
            'status' => 'Inactive',
        ]);
    }

    public function test_moderator_can_delete_chargeable_account()
    {
        $this->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);
        $user = User::factory()->create(['role' => 'moderator']);
        $account = ChargeableAccount::create(['name' => 'To Delete']);

        $response = $this->actingAs($user)->delete(route('chargeable-accounts.destroy', $account));

        $response->assertRedirect(route('chargeable-accounts.index'));
        $this->assertSoftDeleted('chargeable_accounts', [
            'id' => $account->id,
        ]);
    }

    public function test_moderator_can_edit_sub_account()
    {
        $this->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);
        $user = User::factory()->create(['role' => 'moderator']);
        $account = ChargeableAccount::create(['name' => 'Main Account', 'status' => 'Active']);
        $subAccount = $account->subAccounts()->create(['name' => 'Old Sub Name']);

        $response = $this->actingAs($user)->patch(route('sub-accounts.update', $subAccount), [
            'name' => 'Updated Sub Name',
        ]);

        $response->assertRedirect(route('chargeable-accounts.show', $account));
        $this->assertDatabaseHas('sub_accounts', [
            'id' => $subAccount->id,
            'name' => 'Updated Sub Name',
        ]);
    }

    public function test_moderator_can_delete_sub_account()
    {
        $this->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);
        $user = User::factory()->create(['role' => 'moderator']);
        $account = ChargeableAccount::create(['name' => 'Main Account', 'status' => 'Active']);
        $subAccount = $account->subAccounts()->create(['name' => 'Sub to Delete']);

        $response = $this->actingAs($user)->delete(route('sub-accounts.destroy', $subAccount));

        $response->assertRedirect(route('chargeable-accounts.show', $account));
        $this->assertSoftDeleted('sub_accounts', [
            'id' => $subAccount->id,
        ]);
    }
}
