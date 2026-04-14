<?php

namespace Tests\Feature;

use App\Models\ChargeableAccount;
use App\Models\User;
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

    public function test_standard_user_cannot_access_chargeable_accounts_routes()
    {
        $user = User::factory()->create(['role' => 'moderator']);

        $response = $this->actingAs($user)->get(route('chargeable-accounts.index'));
        $response->assertStatus(403);
    }

    public function test_administrator_can_create_chargeable_account()
    {
        $user = User::factory()->create(['role' => 'administrator']);

        $response = $this->actingAs($user)->post(route('chargeable-accounts.store'), [
            'name' => 'Project Alpha',
            'status' => 'Active',
        ]);

        $response->assertRedirect(route('chargeable-accounts.index'));
        $this->assertDatabaseHas('chargeable_accounts', [
            'name' => 'Project Alpha',
            'status' => 'Active',
        ]);
    }

    public function test_administrator_can_update_chargeable_account()
    {
        $user = User::factory()->create(['role' => 'administrator']);
        $account = ChargeableAccount::create(['name' => 'Old Name', 'status' => 'Active']);

        $response = $this->actingAs($user)->patch(route('chargeable-accounts.update', $account), [
            'name' => 'New Name',
            'status' => 'Inactive',
        ]);

        $response->assertRedirect(route('chargeable-accounts.index'));
        $this->assertDatabaseHas('chargeable_accounts', [
            'id' => $account->id,
            'name' => 'New Name',
            'status' => 'Inactive',
        ]);
    }

    public function test_chargeable_account_must_have_unique_name()
    {
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
        $user = User::factory()->create(['role' => 'administrator']);
        $account = ChargeableAccount::create(['name' => 'To Be Deleted']);

        $response = $this->actingAs($user)->delete(route('chargeable-accounts.destroy', $account));

        $response->assertRedirect(route('chargeable-accounts.index'));
        $this->assertSoftDeleted('chargeable_accounts', [
            'id' => $account->id,
        ]);
    }
}
