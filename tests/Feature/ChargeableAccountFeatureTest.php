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
        $user = User::factory()->create(['role' => 'data_logger']);

        $response = $this->actingAs($user)->get(route('chargeable-accounts.index'));
        $response->assertStatus(403);
    }

    public function test_administrator_can_create_chargeable_account()
    {
        $this->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);
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
        $this->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);
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
        $this->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);
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
        $this->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);
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
        $this->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);
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

        $offset = \App\Models\ChargeableAccountOffset::first();

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
        $this->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);
        $user = User::factory()->create(['role' => 'data_logger']);
        $account = ChargeableAccount::create(['name' => 'Another Account', 'status' => 'Active']);

        $response = $this->actingAs($user)->post("/chargeable-accounts/{$account->id}/offsets", [
            'quantity' => 150.50,
        ]);

        $response->assertForbidden();
    }
}
