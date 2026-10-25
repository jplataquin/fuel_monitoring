<?php

namespace App\Mcp\Servers;

use App\Mcp\Resources\AccountBudgetsResource;
use App\Mcp\Resources\AssetResource;
use App\Mcp\Resources\AssetsListResource;
use App\Mcp\Resources\DashboardResource;
use App\Mcp\Resources\RecentFuelOrdersResource;
use App\Mcp\Tools\GetAssetUtilizationLogsTool;
use App\Mcp\Tools\GetFuelOrderDetailsTool;
use App\Mcp\Tools\GetSubAccountAccomplishmentTool;
use App\Mcp\Tools\SearchAssetsTool;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('Fuel Monitoring Server')]
#[Version('1.0.0')]
#[Instructions('Read-only MCP server providing access to assets, fuel orders, chargeable account dashboards, budgets, and sub-account accomplishments.')]
class FuelMonitoringServer extends Server
{
    /**
     * The tools supported by the server.
     *
     * @var array<int, class-string>
     */
    protected array $tools = [
        SearchAssetsTool::class,
        GetAssetUtilizationLogsTool::class,
        GetFuelOrderDetailsTool::class,
        GetSubAccountAccomplishmentTool::class,
    ];

    /**
     * The resources supported by the server.
     *
     * @var array<int, class-string>
     */
    protected array $resources = [
        AssetsListResource::class,
        AssetResource::class,
        RecentFuelOrdersResource::class,
        AccountBudgetsResource::class,
        DashboardResource::class,
    ];

    /**
     * The prompts supported by the server.
     *
     * @var array<int, class-string>
     */
    protected array $prompts = [
        //
    ];
}
