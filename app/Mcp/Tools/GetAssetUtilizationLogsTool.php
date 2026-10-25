<?php

namespace App\Mcp\Tools;

use App\Models\UtilizationEntry;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Name('get_asset_utilization_logs')]
#[IsReadOnly]
#[Description('Retrieve utilization entries and fuel logs for a specific asset within an optional date range.')]
class GetAssetUtilizationLogsTool extends Tool
{
    /**
     * Get the tool's input schema.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'asset_id' => $schema->integer()->description('The ID of the asset')->required(),
            'start_date' => $schema->string()->description('Optional start date in YYYY-MM-DD format'),
            'end_date' => $schema->string()->description('Optional end date in YYYY-MM-DD format'),
            'limit' => $schema->integer()->description('Max number of records to return (defaults to 50)'),
        ];
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $request->validate([
            'asset_id' => ['required', 'integer', 'exists:assets,id'],
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:200'],
        ]);

        $query = UtilizationEntry::with(['chargeableAccount', 'subAccount', 'fuelOrder'])
            ->where('asset_id', $request->get('asset_id'));

        if ($request->has('start_date') && $request->get('start_date')) {
            $query->whereDate('date', '>=', $request->get('start_date'));
        }

        if ($request->has('end_date') && $request->get('end_date')) {
            $query->whereDate('date', '<=', $request->get('end_date'));
        }

        $limit = $request->get('limit', 50);

        $entries = $query->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->take($limit)
            ->get();

        return Response::json($entries);
    }
}
