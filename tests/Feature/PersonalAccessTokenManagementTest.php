<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonalAccessTokenManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_mcp_tokens_page(): void
    {
        $response = $this->get(route('mcp-tokens.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_non_admin_user_cannot_access_mcp_tokens_page(): void
    {
        $user = User::factory()->create(['role' => 'fuel_man']);

        $response = $this->actingAs($user)->get(route('mcp-tokens.index'));

        $response->assertForbidden();
    }

    public function test_admin_can_access_mcp_tokens_page(): void
    {
        $admin = User::factory()->create(['role' => 'administrator']);

        $response = $this->actingAs($admin)->get(route('mcp-tokens.index'));

        $response->assertOk()
            ->assertSee('MCP Personal Access Tokens')
            ->assertSee('Generate New Token');
    }

    public function test_admin_can_create_new_personal_access_token(): void
    {
        $this->withoutMiddleware([ValidateCsrfToken::class]);
        $admin = User::factory()->create(['role' => 'administrator']);
        $assignedUser = User::factory()->create(['role' => 'moderator']);

        $response = $this->actingAs($admin)->post(route('mcp-tokens.store'), [
            'name' => 'Claude Desktop Token',
            'user_id' => $assignedUser->id,
        ]);

        $response->assertRedirect(route('mcp-tokens.index'))
            ->assertSessionHas('plainTextToken')
            ->assertSessionHas('tokenName', 'Claude Desktop Token');

        $this->assertDatabaseHas('personal_access_tokens', [
            'name' => 'Claude Desktop Token',
            'tokenable_id' => $assignedUser->id,
            'tokenable_type' => User::class,
        ]);
    }

    public function test_admin_can_revoke_personal_access_token(): void
    {
        $this->withoutMiddleware([ValidateCsrfToken::class]);
        $admin = User::factory()->create(['role' => 'administrator']);
        $token = $admin->createToken('Temporary Agent Token');

        $this->assertDatabaseHas('personal_access_tokens', [
            'name' => 'Temporary Agent Token',
        ]);

        $response = $this->actingAs($admin)->delete(route('mcp-tokens.destroy', $token->accessToken));

        $response->assertRedirect(route('mcp-tokens.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseMissing('personal_access_tokens', [
            'name' => 'Temporary Agent Token',
        ]);
    }
}
