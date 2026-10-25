<?php

namespace App\Mcp\Resources;

use App\Models\FuelOrder;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\MimeType;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Uri;
use Laravel\Mcp\Server\Resource;

#[Name('recent_fuel_orders')]
#[Uri('fuel_orders://recent')]
#[MimeType('application/json')]
#[Description('List of the 20 most recent fuel orders with their status, asset, and sub-account details.')]
class RecentFuelOrdersResource extends Resource
{
    /**
     * Handle the resource request.
     */
    public function handle(Request $request): Response
    {
        $orders = FuelOrder::with(['asset', 'subAccount'])
            ->latest()
            ->take(20)
            ->get();

        return Response::json($orders);
    }
}
