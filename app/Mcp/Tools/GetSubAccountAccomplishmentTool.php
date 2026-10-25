<?php

namespace App\Mcp\Tools;

use App\Models\SubAccount;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Name('get_sub_account_accomplishment')]
#[IsReadOnly]
#[Description('Retrieve accomplishment progress, target quantity, unit, and accomplishment registry logs for a specific sub-account.')]
class GetSubAccountAccomplishmentTool extends Tool
{
    /**
     * Get the tool's input schema.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'sub_account_id' => $schema->integer()->description('The ID of the sub-account')->required(),
        ];
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $request->validate([
            'sub_account_id' => ['required', 'integer'],
        ]);

        $subAccount = SubAccount::with([
            'chargeableAccount',
            'accomplishments' => fn ($query) => $query->orderBy('date_at', 'desc')->orderBy('id', 'desc'),
        ])->find($request->get('sub_account_id'));

        if (! $subAccount) {
            return Response::error("Sub-account with ID [{$request->get('sub_account_id')}] not found.");
        }

        $latestAccomplishment = $subAccount->accomplishments->first();

        return Response::json([
            'sub_account_id' => $subAccount->id,
            'name' => $subAccount->name,
            'type' => $subAccount->type,
            'chargeable_account' => [
                'id' => $subAccount->chargeableAccount->id ?? null,
                'name' => $subAccount->chargeableAccount->name ?? null,
            ],
            'target_quantity' => $subAccount->quantity,
            'unit' => $subAccount->unit,
            'accomplishment_percentage' => $subAccount->accomplishment,
            'latest_accomplishment' => $latestAccomplishment ? [
                'id' => $latestAccomplishment->id,
                'quantity' => $latestAccomplishment->quantity,
                'date_at' => $latestAccomplishment->date_at,
                'reference_id' => $latestAccomplishment->reference_id,
                'remarks' => $latestAccomplishment->remarks,
            ] : null,
            'history' => $subAccount->accomplishments->map(fn ($item) => [
                'id' => $item->id,
                'quantity' => $item->quantity,
                'date_at' => $item->date_at,
                'reference_id' => $item->reference_id,
                'remarks' => $item->remarks,
            ]),
        ]);
    }
}
