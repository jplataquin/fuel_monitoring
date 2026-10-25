<?php

namespace App\Mcp\Tools;

use App\Models\FuelOrder;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Name('get_fuel_order_details')]
#[IsReadOnly]
#[Description('Retrieve complete details of a specific fuel order, including related asset, account, and utilization entries.')]
class GetFuelOrderDetailsTool extends Tool
{
    /**
     * Get the tool's input schema.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'fuel_order_id' => $schema->integer()->description('The ID of the fuel order')->required(),
        ];
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $request->validate([
            'fuel_order_id' => ['required', 'integer'],
        ]);

        $order = FuelOrder::with([
            'asset.assetType',
            'chargeableAccount',
            'subAccount',
            'utilizationEntries',
            'creator',
            'actualizer',
            'voider',
            'waivedBy',
        ])->find($request->get('fuel_order_id'));

        if (! $order) {
            return Response::error("Fuel Order with ID [{$request->get('fuel_order_id')}] not found.");
        }

        return Response::json($order);
    }
}
