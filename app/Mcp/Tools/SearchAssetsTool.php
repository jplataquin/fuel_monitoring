<?php

namespace App\Mcp\Tools;

use App\Models\Asset;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Name('search_assets')]
#[IsReadOnly]
#[Description('Search for assets by fleet number or plate number, optionally filtered by asset type ID.')]
class SearchAssetsTool extends Tool
{
    /**
     * Get the tool's input schema.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string()->description('The search query (matches fleet_no or plate_no)')->required(),
            'asset_type_id' => $schema->integer()->description('Optional asset type ID filter'),
        ];
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $request->validate([
            'query' => ['required', 'string'],
            'asset_type_id' => ['nullable', 'integer'],
        ]);

        $searchTerm = $request->get('query');
        $assetTypeId = $request->get('asset_type_id');

        $query = Asset::with('assetType')
            ->where(function ($q) use ($searchTerm) {
                $q->where('fleet_no', 'like', "%{$searchTerm}%")
                    ->orWhere('plate_no', 'like', "%{$searchTerm}%");
            });

        if ($assetTypeId) {
            $query->where('asset_type_id', $assetTypeId);
        }

        $assets = $query->get();

        return Response::json($assets);
    }
}
