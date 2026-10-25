<?php

namespace App\Mcp\Resources;

use App\Models\Asset;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\MimeType;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Contracts\HasUriTemplate;
use Laravel\Mcp\Server\Resource;
use Laravel\Mcp\Support\UriTemplate;

#[Name('asset_details')]
#[MimeType('application/json')]
#[Description('Details of a specific asset, including tank capacity, fuel type, and latest readings.')]
class AssetResource extends Resource implements HasUriTemplate
{
    /**
     * Get the URI pattern for the resource template.
     */
    public function uriTemplate(): UriTemplate
    {
        return new UriTemplate('assets://{asset_id}');
    }

    /**
     * Handle the resource request.
     */
    public function handle(Request $request): Response
    {
        $assetId = $request->get('asset_id');
        $asset = Asset::with('assetType')->find($assetId);

        if (! $asset) {
            return Response::error("Asset with ID [{$assetId}] not found.");
        }

        return Response::json($asset);
    }
}
