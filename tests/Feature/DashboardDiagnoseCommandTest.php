<?php

namespace Tests\Feature;

use App\Models\ChargeableAccount;
use App\Models\SubAccount;
use App\Models\SubAccountBudget;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardDiagnoseCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_diagnose_command_runs_successfully(): void
    {
        $user = User::factory()->create(['role' => 'administrator']);
        $account = ChargeableAccount::create([
            'name' => 'Diagnose Project',
            'classification' => 'Running',
            'status' => 'Active',
        ]);
        $sub = SubAccount::create([
            'chargeable_account_id' => $account->id,
            'name' => 'Diagnose Sub',
        ]);

        SubAccountBudget::create([
            'sub_account_id' => $sub->id,
            'budget_quantity' => 1000.0,
            'status' => 'Approved',
            'created_by' => $user->id,
        ]);

        $this->artisan('dashboard:diagnose')
            ->assertExitCode(0)
            ->expectsOutputToContain('DASHBOARD BUDGET VS SUB-ACCOUNTS DIAGNOSTIC')
            ->expectsOutputToContain('Diagnose Project');
    }

    public function test_dashboard_diagnose_command_filters_by_account_argument(): void
    {
        $account = ChargeableAccount::create([
            'name' => 'Target Account',
            'classification' => 'Running',
            'status' => 'Active',
        ]);

        $this->artisan('dashboard:diagnose', ['account' => $account->id])
            ->assertExitCode(0)
            ->expectsOutputToContain('Target Account');
    }
}
