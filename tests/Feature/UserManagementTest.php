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
        $this->withoutExceptionHandling();
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
}
