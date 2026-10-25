<?php

namespace App\Mcp\Tools;

use App\Traits\DashboardDataTrait;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Name('get_asset_performance')]
#[IsReadOnly]
#[Description('Retrieve asset performance and variance data comparing estimated fuel orders (say_quantity) against actual consumption (actual_quantity).')]
class GetAssetPerformanceTool extends Tool
{
    use DashboardDataTrait;

    /**
     * Get the tool's input schema.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'start_date' => $schema->string()->description('Optional start date in YYYY-MM-DD format'),
            'end_date' => $schema->string()->description('Optional end date in YYYY-MM-DD format'),
            'asset_id' => $schema->integer()->description('Optional specific asset ID to filter'),
        ];
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $request->validate([
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d'],
            'asset_id' => ['nullable', 'integer', 'exists:assets,id'],
        ]);

        $dateFrom = $request->get('start_date');
        $dateTo = $request->get('end_date');
        $assetId = $request->get('asset_id');

        $performanceData = $this->getAssetVarianceData($dateFrom, $dateTo, $assetId);

        return Response::json($performanceData);
    }
}
