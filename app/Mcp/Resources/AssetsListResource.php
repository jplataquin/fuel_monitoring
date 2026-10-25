<?php

namespace App\Mcp\Resources;

use App\Models\Asset;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\MimeType;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Uri;
use Laravel\Mcp\Server\Resource;

#[Name('assets_list')]
#[Uri('assets://list')]
#[MimeType('application/json')]
#[Description('List of all active assets, vehicles, and equipment with their types and current status.')]
class AssetsListResource extends Resource
{
    /**
     * Handle the resource request.
     */
    public function handle(Request $request): Response
    {
        $assets = Asset::with('assetType')
            ->orderBy('fleet_no')
            ->get();

        return Response::json($assets);
    }
}
