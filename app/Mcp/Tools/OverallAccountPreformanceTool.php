<?php

namespace App\Mcp\Tools;

use App\Models\ChargeableAccount;
use App\Traits\DashboardDataTrait;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Name('overall_account_preformance')]
#[IsReadOnly]
#[Description('Retrieve overall account performance data including Summary Statistics, projection summary, and sub-account breakdown matching the /dashboard/account/{account_id}/sub-accounts view.')]
class OverallAccountPreformanceTool extends Tool
{
    use DashboardDataTrait;

    /**
     * Get the tool's input schema.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'account_id' => $schema->integer()->description('The ID of the Chargeable Account')->required(),
        ];
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $request->validate([
            'account_id' => ['required_without:id', 'nullable', 'integer'],
            'id' => ['required_without:account_id', 'nullable', 'integer'],
        ]);

        $accountId = $request->get('account_id') ?? $request->get('id');
        $account = ChargeableAccount::find($accountId);

        if (! $account) {
            return Response::error("Chargeable Account with ID [{$accountId}] not found.");
        }

        $performanceData = $this->getOverallAccountPerformanceData($account);

        return Response::json($performanceData);
    }
}
