<?php

namespace App\Mcp\Resources;

use App\Models\ChargeableAccount;
use App\Traits\DashboardDataTrait;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\MimeType;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Contracts\HasUriTemplate;
use Laravel\Mcp\Server\Resource;
use Laravel\Mcp\Support\UriTemplate;

#[Name('dashboard')]
#[MimeType('application/json')]
#[Description('Aggregated sub-account dashboard data for a Chargeable Account (matching /dashboard/account/{id}/sub-accounts).')]
class DashboardResource extends Resource implements HasUriTemplate
{
    use DashboardDataTrait;

    /**
     * Get the URI pattern for the resource template.
     */
    public function uriTemplate(): UriTemplate
    {
        return new UriTemplate('dashboard://{account_id}');
    }

    /**
     * Handle the resource request.
     */
    public function handle(Request $request): Response
    {
        $accountId = $request->get('account_id');
        $account = ChargeableAccount::find($accountId);

        if (! $account) {
            return Response::error("Chargeable Account with ID [{$accountId}] not found.");
        }

        $dashboardData = $this->getSubAccountDashboardData($account);

        return Response::json([
            'account' => [
                'id' => $account->id,
                'name' => $account->name,
                'classification' => $account->classification,
            ],
            ...$dashboardData,
        ]);
    }
}
