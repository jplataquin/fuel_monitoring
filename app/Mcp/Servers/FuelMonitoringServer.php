<?php

namespace App\Mcp\Servers;

use App\Mcp\Resources\AccountBudgetsResource;
use App\Mcp\Resources\AssetResource;
use App\Mcp\Resources\AssetsListResource;
use App\Mcp\Resources\DashboardResource;
use App\Mcp\Resources\RecentFuelOrdersResource;
use App\Mcp\Tools\AccountsTool;
use App\Mcp\Tools\GetAccountsTool;
use App\Mcp\Tools\GetAssetPerformanceTool;
use App\Mcp\Tools\GetAssetUtilizationLogsTool;
use App\Mcp\Tools\GetFuelOrderDetailsTool;
use App\Mcp\Tools\GetSubAccountAccomplishmentTool;
use App\Mcp\Tools\OverallAccountPerformanceTool;
use App\Mcp\Tools\OverallAccountPreformanceTool;
use App\Mcp\Tools\SearchAccountsTool;
use App\Mcp\Tools\SearchAssetsTool;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('Fuel Monitoring Server')]
#[Version('1.0.0')]
class FuelMonitoringServer extends Server
{
    /**
     * Detailed instructions describing the server domain, purpose, and tool selection guidelines.
     */
    protected string $instructions = <<<'MARKDOWN'
# Fuel Monitoring System — AI Agent Instructions

You are interacting with the official **Fuel Monitoring Management System** MCP server. This server provides read-only access to fleet assets, equipment utilization logs, fuel replenishment orders, project account budgets, and milestone accomplishments.

---

## Domain Concepts & Rules

1. **Assets & Fleet**:
   - Assets are vehicles (trucks, pickups) or heavy equipment (excavators, loaders, generators) identified by a unique `fleet_no` and optional `plate_no`.
   - Each asset has an associated fuel type (e.g., Diesel), tank capacity, and consumption factors (`fuel_factor_km` or `fuel_factor_hr`).

2. **Say Quantity vs. Actual Quantity**:
   - **Say Quantity (`say_quantity`)**: The mathematically estimated/prescribed fuel volume computed from logged utilization distance (kilometers) or operating duration (engine hours).
   - **Actual Quantity (`actual_quantity`)**: The physical fuel volume dispensed into the asset's tank during replenishment.

3. **Performance & Variance Thresholds**:
   - Variance is computed as: `((actual_quantity - say_quantity) / say_quantity) * 100`.
   - **Critical Over-Consumption (`variance >= 10%`)**: The asset consumed significantly more fuel than expected. This indicates potential mechanical inefficiency, fuel siphoning, or excessive idling.
   - **Under-Consumption (`variance < 0%`)**: The asset was replenished with less fuel than estimated, or operating conditions were more efficient than standard factors.

4. **Accounts, Budgets & Accomplishments**:
   - **Chargeable Accounts**: High-level cost centers or projects (e.g., Highway Extension, Mining Site A).
   - **Sub-Accounts**: Granular work phases or task lines linked to a parent account.
   - **Accomplishments**: Quantitative project completion progress (e.g., cu.m of earthworks, linear meters) tracked against target quantities.

---

## Workflow & Tool Selection Guidelines

- **Identifying Assets**:
  - When the user refers to an asset by name, number, or category (e.g., "Truck 12", "grader"), always begin by calling `search_assets` with the search query to resolve the exact `id` and `fleet_no`.
  - To view complete asset technical specifications, inspect `assets://{asset_id}`.

- **Evaluating Performance & Fuel Anomalies**:
  - Use `get_asset_performance` to evaluate consumption variance across all assets or for a specific `asset_id`.
  - Highlight any assets with variance $\ge 10\%$ as critical attention points.

- **Investigating Daily Usage**:
  - When asked about trips, operator names, meter readings, or specific dates, call `get_asset_utilization_logs` with the `asset_id` and optional `start_date` / `end_date`.

- **Checking Accounts & Performance**:
  - Use `search_accounts` (or `get_accounts` / `accounts`) with optional filters (`name` for partial match, `id`, `status`) to query Chargeable Accounts.
  - Call `overall_account_preformance` (or `overall_account_performance`) with `account_id` to retrieve comprehensive metrics matching `/dashboard/account/{account_id}/sub-accounts`, including Summary Statistics, Projection Summary (savings, excess, net), and the complete Sub-Account Breakdown.

- **Checking Project Budgets & Consumption Dashboards**:
  - Use `dashboard://{account_id}` to retrieve comprehensive sub-account consumption graphs, remaining balances, and allocation metrics matching the web dashboard.
  - Use `accounts://budgets` for an overview of all active chargeable account allocations.

- **Checking Milestone Accomplishments**:
  - Call `get_sub_account_accomplishment` using the `sub_account_id` to inspect target quantities, unit of measurement, latest recorded accomplishment, and calculated completion percentage.

- **Auditing Orders**:
  - Use `fuel_orders://recent` to view the 20 most recent fuel order transactions.
  - Call `get_fuel_order_details` with `fuel_order_id` to inspect attached utilization slips, actualizer details, and any waiver or void records.

---

## Operating Directives
- All endpoints on this server are strictly **read-only**.
- Always ground answers in the data retrieved from the server. Never hallucinate or assume meter readings or fuel quantities without fetching.
MARKDOWN;

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
        GetAssetPerformanceTool::class,
        SearchAccountsTool::class,
        GetAccountsTool::class,
        AccountsTool::class,
        OverallAccountPreformanceTool::class,
        OverallAccountPerformanceTool::class,
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
