<?php

namespace Tests\Feature;

use App\Models\ChargeableAccount;
use App\Models\SubAccount;
use App\Models\SubAccountNote;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubAccountNoteTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $viewer;

    protected User $dataLogger;

    protected ChargeableAccount $account;

    protected SubAccount $subAccount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([ValidateCsrfToken::class]);

        $this->admin = User::factory()->create(['role' => 'administrator']);
        $this->viewer = User::factory()->create(['role' => 'viewer']);
        $this->dataLogger = User::factory()->create(['role' => 'data_logger']);

        $this->account = ChargeableAccount::create([
            'name' => 'Test Account',
            'status' => 'Active',
            'classification' => 'Running',
        ]);

        $this->subAccount = SubAccount::create([
            'chargeable_account_id' => $this->account->id,
            'name' => 'Test Sub Account',
        ]);
    }

    public function test_authorized_user_can_create_sub_account_note(): void
    {
        $response = $this->actingAs($this->dataLogger)->postJson(route('sub-accounts.notes.store', $this->subAccount), [
            'note' => 'This is a test note.',
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'note' => 'This is a test note.',
            ]);

        $this->assertDatabaseHas('sub_account_notes', [
            'sub_account_id' => $this->subAccount->id,
            'user_id' => $this->dataLogger->id,
            'note' => 'This is a test note.',
        ]);
    }

    public function test_viewer_cannot_create_sub_account_note(): void
    {
        $response = $this->actingAs($this->viewer)->postJson(route('sub-accounts.notes.store', $this->subAccount), [
            'note' => 'This note should fail.',
        ]);

        $response->assertStatus(403);

        $this->assertDatabaseMissing('sub_account_notes', [
            'note' => 'This note should fail.',
        ]);
    }

    public function test_viewer_can_view_sub_account_notes(): void
    {
        SubAccountNote::create([
            'sub_account_id' => $this->subAccount->id,
            'user_id' => $this->admin->id,
            'note' => 'Existing note',
        ]);

        $response = $this->actingAs($this->viewer)->getJson(route('sub-accounts.notes.index', $this->subAccount));

        $response->assertStatus(200)
            ->assertJsonFragment([
                'note' => 'Existing note',
            ]);
    }

    public function test_note_validation_rules(): void
    {
        // Required validation
        $response = $this->actingAs($this->admin)->postJson(route('sub-accounts.notes.store', $this->subAccount), [
            'note' => '',
        ]);
        $response->assertStatus(422)->assertJsonValidationErrors(['note']);

        // Max length validation (1000)
        $response = $this->actingAs($this->admin)->postJson(route('sub-accounts.notes.store', $this->subAccount), [
            'note' => str_repeat('a', 1001),
        ]);
        $response->assertStatus(422)->assertJsonValidationErrors(['note']);
    }

    public function test_dashboard_renders_notes_button(): void
    {
        $response = $this->actingAs($this->admin)->get(route('dashboard.sub-accounts', $this->account));

        $response->assertStatus(200);
        $response->assertSee('bi-chat-left-text');
        $response->assertSee('subAccountNotesModal');
    }
}
