<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_access_user_creation_pages()
    {
        $admin = User::factory()->create(['role' => 'administrator']);

        $response = $this->actingAs($admin)->get(route('users.create-data-logger'));
        $response->assertStatus(200);

        $response = $this->actingAs($admin)->get(route('users.create-moderator'));
        $response->assertStatus(200);
    }

    public function test_moderator_can_access_data_logger_creation_page()
    {
        $moderator = User::factory()->create(['role' => 'moderator']);

        $response = $this->actingAs($moderator)->get(route('users.create-data-logger'));
        $response->assertStatus(200);
    }

    public function test_moderator_cannot_access_moderator_creation_page()
    {
        $moderator = User::factory()->create(['role' => 'moderator']);

        $response = $this->actingAs($moderator)->get(route('users.create-moderator'));
        $response->assertStatus(403);
    }

    public function test_administrator_can_store_moderator()
    {
        $this->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]); // Disable CSRF middleware to bypass CSRF issues in this test environment
        $admin = User::factory()->create(['role' => 'administrator']);

        $response = $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'New Moderator',
            'email' => 'mod@example.com',
            'role' => 'moderator',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', ['email' => 'mod@example.com', 'role' => 'moderator']);
    }

    public function test_moderator_cannot_store_moderator()
    {
        $this->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]); // Disable CSRF middleware to bypass CSRF issues in this test environment
        $moderator = User::factory()->create(['role' => 'moderator']);

        $response = $this->actingAs($moderator)->post(route('users.store'), [
            'name' => 'New Moderator',
            'email' => 'mod@example.com',
            'role' => 'moderator',
            'password' => 'password123',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('users', ['email' => 'mod@example.com']);
    }

    public function test_moderator_can_store_data_logger()
    {
        $this->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]); // Disable CSRF middleware to bypass CSRF issues in this test environment
        $moderator = User::factory()->create(['role' => 'moderator']);

        $response = $this->actingAs($moderator)->post(route('users.store'), [
            'name' => 'New Data Logger',
            'email' => 'dl@example.com',
            'role' => 'data_logger',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', ['email' => 'dl@example.com', 'role' => 'data_logger']);
    }

    public function test_administrator_can_update_user_role()
    {
        $this->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);
        $admin = User::factory()->create(['role' => 'administrator']);
        $user = User::factory()->create(['role' => 'data_logger']);

        $response = $this->actingAs($admin)->patch(route('users.update', $user), [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'role' => 'moderator',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', ['email' => 'updated@example.com', 'role' => 'moderator']);
    }

    public function test_moderator_cannot_update_user_role()
    {
        $this->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);
        $moderator = User::factory()->create(['role' => 'moderator']);
        $user = User::factory()->create(['role' => 'data_logger']);

        $response = $this->actingAs($moderator)->patch(route('users.update', $user), [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'role' => 'administrator',
        ]);

        $response->assertRedirect(route('users.index'));
        // The name and email should be updated, but the role should remain 'data_logger'
        $this->assertDatabaseHas('users', ['email' => 'updated@example.com', 'role' => 'data_logger']);
    }
}
