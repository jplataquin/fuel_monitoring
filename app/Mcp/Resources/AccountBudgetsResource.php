<?php

namespace App\Mcp\Resources;

use App\Models\ChargeableAccount;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\MimeType;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Uri;
use Laravel\Mcp\Server\Resource;

#[Name('account_budgets')]
#[Uri('accounts://budgets')]
#[MimeType('application/json')]
#[Description('Summary of Chargeable Accounts and their Sub-Account budget allocations and details.')]
class AccountBudgetsResource extends Resource
{
    /**
     * Handle the resource request.
     */
    public function handle(Request $request): Response
    {
        $accounts = ChargeableAccount::with(['subAccounts.budgets'])
            ->where('status', 'Active')
            ->orderBy('name')
            ->get();

        return Response::json($accounts);
    }
}
