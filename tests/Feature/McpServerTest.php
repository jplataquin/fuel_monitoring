<?php

namespace Tests\Feature;

use App\Mcp\Resources\AccountBudgetsResource;
use App\Mcp\Resources\AssetResource;
use App\Mcp\Resources\AssetsListResource;
use App\Mcp\Resources\DashboardResource;
use App\Mcp\Resources\RecentFuelOrdersResource;
use App\Mcp\Servers\FuelMonitoringServer;
use App\Mcp\Tools\AccountsTool;
use App\Mcp\Tools\GetAccountsTool;
use App\Mcp\Tools\GetAssetPerformanceTool;
use App\Mcp\Tools\GetAssetUtilizationLogsTool;
use App\Mcp\Tools\GetFuelOrderDetailsTool;
use App\Mcp\Tools\GetSubAccountAccomplishmentTool;
use App\Mcp\Tools\OverallAccountPerformanceTool;
use App\Mcp\Tools\SearchAccountsTool;
use App\Mcp\Tools\SearchAssetsTool;
use App\Models\AccomplishmentRegistry;
use App\Models\Asset;
use App\Models\AssetType;
use App\Models\ChargeableAccount;
use App\Models\FuelOrder;
use App\Models\SubAccount;
use App\Models\SubAccountBudget;
use App\Models\User;
use App\Models\UtilizationEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class McpServerTest extends TestCase
{
    use RefreshDatabase;

    public function test_mcp_route_requires_sanctum_authentication(): void
    {
        $response = $this->postJson('/mcp', [
            'jsonrpc' => '2.0',
            'method' => 'initialize',
            'id' => 1,
            'params' => [
                'protocolVersion' => '2024-11-05',
                'capabilities' => [],
                'clientInfo' => ['name' => 'test', 'version' => '1.0'],
            ],
        ]);

        $response->assertUnauthorized();
    }

    public function test_mcp_route_succeeds_with_sanctum_bearer_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/mcp', [
                'jsonrpc' => '2.0',
                'method' => 'initialize',
                'id' => 1,
                'params' => [
                    'protocolVersion' => '2024-11-05',
                    'capabilities' => [],
                    'clientInfo' => ['name' => 'test', 'version' => '1.0'],
                ],
            ]);

        $response->assertOk()
            ->assertJsonPath('result.serverInfo.name', 'Fuel Monitoring Server')
            ->assertJsonPath('result.instructions', fn ($instructions) => str_contains($instructions, 'Critical Over-Consumption'));
    }

    public function test_mcp_server_lists_all_tools_and_resources(): void
    {
        $user = User::factory()->create();

        FuelMonitoringServer::actingAs($user)->tools()->assertRegistered([
            SearchAssetsTool::class,
            GetAssetUtilizationLogsTool::class,
            GetFuelOrderDetailsTool::class,
            GetSubAccountAccomplishmentTool::class,
            GetAssetPerformanceTool::class,
            SearchAccountsTool::class,
            GetAccountsTool::class,
            AccountsTool::class,
            OverallAccountPerformanceTool::class,
        ]);

        FuelMonitoringServer::actingAs($user)->resources()->assertRegistered([
            AssetsListResource::class,
            RecentFuelOrdersResource::class,
            AccountBudgetsResource::class,
        ]);
    }

    public function test_assets_list_resource(): void
    {
        $user = User::factory()->create();
        $type = AssetType::create(['name' => 'Dump Truck']);
        Asset::create([
            'fleet_no' => 'DT-101',
            'plate_no' => 'ABC-1234',
            'asset_type_id' => $type->id,
            'tank_capacity' => 150,
            'fuel_type' => 'Diesel',
        ]);

        $response = FuelMonitoringServer::actingAs($user)->resource(AssetsListResource::class);

        $response->assertOk()
            ->assertSee('DT-101')
            ->assertSee('ABC-1234');
    }

    public function test_asset_resource(): void
    {
        $user = User::factory()->create();
        $type = AssetType::create(['name' => 'Excavator']);
        $asset = Asset::create([
            'fleet_no' => 'EX-201',
            'plate_no' => 'XYZ-9876',
            'asset_type_id' => $type->id,
            'tank_capacity' => 250,
            'fuel_type' => 'Diesel',
        ]);

        $response = FuelMonitoringServer::actingAs($user)->resource(AssetResource::class, [
            'asset_id' => $asset->id,
        ]);

        $response->assertOk()
            ->assertSee('EX-201')
            ->assertSee('XYZ-9876')
            ->assertSee('250');
    }

    public function test_recent_fuel_orders_resource(): void
    {
        $user = User::factory()->create();
        $type = AssetType::create(['name' => 'Dump Truck']);
        $asset = Asset::create([
            'fleet_no' => 'DT-102',
            'asset_type_id' => $type->id,
            'tank_capacity' => 100,
            'fuel_type' => 'Diesel',
        ]);

        $account = ChargeableAccount::create(['name' => 'Project Alpha', 'classification' => 'Running']);
        $subAccount = SubAccount::create(['name' => 'Road Work', 'chargeable_account_id' => $account->id]);

        FuelOrder::create([
            'asset_id' => $asset->id,
            'chargeable_account_id' => $account->id,
            'sub_account_id' => $subAccount->id,
            'status' => 'DONE',
            'actual_quantity' => 85.5,
            'say_quantity' => 90,
        ]);

        $response = FuelMonitoringServer::actingAs($user)->resource(RecentFuelOrdersResource::class);

        $response->assertOk()
            ->assertSee('85.5')
            ->assertSee('Road Work');
    }

    public function test_account_budgets_resource(): void
    {
        $user = User::factory()->create();
        $account = ChargeableAccount::create(['name' => 'Account Beta', 'status' => 'Active', 'classification' => 'Running']);
        $subAccount = SubAccount::create(['name' => 'Phase 1', 'chargeable_account_id' => $account->id]);
        SubAccountBudget::create([
            'sub_account_id' => $subAccount->id,
            'budget_quantity' => 1000,
            'status' => 'Approved',
        ]);

        $response = FuelMonitoringServer::actingAs($user)->resource(AccountBudgetsResource::class);

        $response->assertOk()
            ->assertSee('Account Beta')
            ->assertSee('Phase 1');
    }

    public function test_dashboard_resource(): void
    {
        $user = User::factory()->create();
        $account = ChargeableAccount::create(['name' => 'Account Gamma', 'classification' => 'Running']);
        $subAccount = SubAccount::create(['name' => 'Site Prep', 'chargeable_account_id' => $account->id]);
        SubAccountBudget::create([
            'sub_account_id' => $subAccount->id,
            'budget_quantity' => 500,
            'status' => 'Approved',
        ]);

        $response = FuelMonitoringServer::actingAs($user)->resource(DashboardResource::class, [
            'account_id' => $account->id,
        ]);

        $response->assertOk()
            ->assertSee('Account Gamma')
            ->assertSee('Site Prep')
            ->assertSee('subAccountData');
    }

    public function test_search_assets_tool(): void
    {
        $user = User::factory()->create();
        $type = AssetType::create(['name' => 'Truck']);
        Asset::create([
            'fleet_no' => 'TRK-55',
            'plate_no' => 'XYZ-5555',
            'asset_type_id' => $type->id,
            'tank_capacity' => 120,
            'fuel_type' => 'Diesel',
        ]);
        Asset::create([
            'fleet_no' => 'GEN-01',
            'plate_no' => 'ABC-1111',
            'asset_type_id' => $type->id,
            'tank_capacity' => 50,
            'fuel_type' => 'Diesel',
        ]);

        $response = FuelMonitoringServer::actingAs($user)->tool(SearchAssetsTool::class, [
            'query' => 'TRK',
        ]);

        $response->assertOk()
            ->assertSee('TRK-55')
            ->assertDontSee('GEN-01');
    }

    public function test_get_asset_utilization_logs_tool(): void
    {
        $user = User::factory()->create();
        $type = AssetType::create(['name' => 'Truck']);
        $asset = Asset::create([
            'fleet_no' => 'TRK-99',
            'asset_type_id' => $type->id,
            'tank_capacity' => 120,
            'fuel_type' => 'Diesel',
        ]);

        UtilizationEntry::create([
            'asset_id' => $asset->id,
            'reference' => 'REF-101',
            'driver_operator_name' => 'John Doe',
            'particulars' => 'Material hauling',
            'date' => '2026-10-20',
            'start_time' => '08:00',
            'end_time' => '17:00',
            'start_kilometer_reading' => 100,
            'end_kilometer_reading' => 150,
            'calculation_type' => 'kilometer',
        ]);

        $response = FuelMonitoringServer::actingAs($user)->tool(GetAssetUtilizationLogsTool::class, [
            'asset_id' => $asset->id,
        ]);

        $response->assertOk()
            ->assertSee('100')
            ->assertSee('150');
    }

    public function test_get_fuel_order_details_tool(): void
    {
        $user = User::factory()->create();
        $type = AssetType::create(['name' => 'Van']);
        $asset = Asset::create([
            'fleet_no' => 'VN-01',
            'asset_type_id' => $type->id,
            'tank_capacity' => 60,
            'fuel_type' => 'Diesel',
        ]);

        $order = FuelOrder::create([
            'asset_id' => $asset->id,
            'status' => 'PENDING',
            'say_quantity' => 45,
            'remarks' => 'Routine refill',
        ]);

        $response = FuelMonitoringServer::actingAs($user)->tool(GetFuelOrderDetailsTool::class, [
            'fuel_order_id' => $order->id,
        ]);

        $response->assertOk()
            ->assertSee('Routine refill')
            ->assertSee('VN-01');
    }

    public function test_get_sub_account_accomplishment_tool(): void
    {
        $user = User::factory()->create();
        $account = ChargeableAccount::create(['name' => 'Highway Project', 'classification' => 'Running']);
        $subAccount = SubAccount::create([
            'name' => 'Earthworks',
            'chargeable_account_id' => $account->id,
            'quantity' => 1000,
            'unit' => 'cu.m',
        ]);

        AccomplishmentRegistry::create([
            'sub_account_id' => $subAccount->id,
            'quantity' => 250,
            'date_at' => '2026-10-22',
            'reference_id' => 'REF-001',
            'remarks' => 'First milestone reached',
        ]);

        $response = FuelMonitoringServer::actingAs($user)->tool(GetSubAccountAccomplishmentTool::class, [
            'sub_account_id' => $subAccount->id,
        ]);

        $response->assertOk()
            ->assertSee('Earthworks')
            ->assertSee('REF-001')
            ->assertSee('25');
    }

    public function test_get_asset_performance_tool(): void
    {
        $user = User::factory()->create();
        $type = AssetType::create(['name' => 'Dump Truck']);
        $asset = Asset::create([
            'fleet_no' => 'DT-500',
            'plate_no' => 'XYZ-500',
            'asset_type_id' => $type->id,
            'tank_capacity' => 200,
            'fuel_type' => 'Diesel',
        ]);

        $account = ChargeableAccount::create(['name' => 'Project Delta', 'classification' => 'Running']);
        $subAccount = SubAccount::create(['name' => 'Hauling', 'chargeable_account_id' => $account->id]);

        FuelOrder::create([
            'asset_id' => $asset->id,
            'chargeable_account_id' => $account->id,
            'sub_account_id' => $subAccount->id,
            'status' => 'DONE',
            'say_quantity' => 100,
            'actual_quantity' => 110,
        ]);

        $response = FuelMonitoringServer::actingAs($user)->tool(GetAssetPerformanceTool::class, [
            'asset_id' => $asset->id,
        ]);

        $response->assertOk()
            ->assertSee('DT-500')
            ->assertSee('100')
            ->assertSee('110')
            ->assertSee('10');
    }

    public function test_search_accounts_tool_with_name_filter(): void
    {
        $user = User::factory()->create();
        ChargeableAccount::create(['name' => 'Highway Maintenance Project', 'status' => 'Active', 'classification' => 'Running']);
        ChargeableAccount::create(['name' => 'Office Building Construction', 'status' => 'Active', 'classification' => 'Running']);

        $response = FuelMonitoringServer::actingAs($user)->tool(SearchAccountsTool::class, [
            'name' => 'Highway',
        ]);

        $response->assertOk()
            ->assertSee('Highway Maintenance Project')
            ->assertDontSee('Office Building Construction');
    }

    public function test_search_accounts_tool_with_id_filter(): void
    {
        $user = User::factory()->create();
        $account1 = ChargeableAccount::create(['name' => 'First Account', 'status' => 'Active', 'classification' => 'Running']);
        $account2 = ChargeableAccount::create(['name' => 'Second Account', 'status' => 'Active', 'classification' => 'Running']);

        $response = FuelMonitoringServer::actingAs($user)->tool(SearchAccountsTool::class, [
            'id' => $account1->id,
        ]);

        $response->assertOk()
            ->assertSee('First Account')
            ->assertDontSee('Second Account');
    }

    public function test_search_accounts_tool_with_status_filter(): void
    {
        $user = User::factory()->create();
        ChargeableAccount::create(['name' => 'Active Account One', 'status' => 'Active', 'classification' => 'Running']);
        ChargeableAccount::create(['name' => 'Inactive Account Two', 'status' => 'Inactive', 'classification' => 'Running']);

        $response = FuelMonitoringServer::actingAs($user)->tool(SearchAccountsTool::class, [
            'status' => 'Inactive',
        ]);

        $response->assertOk()
            ->assertSee('Inactive Account Two')
            ->assertDontSee('Active Account One');
    }

    public function test_get_accounts_and_accounts_aliases(): void
    {
        $user = User::factory()->create();
        ChargeableAccount::create(['name' => 'Alpha Core', 'status' => 'Active', 'classification' => 'Running']);

        $responseGet = FuelMonitoringServer::actingAs($user)->tool(GetAccountsTool::class, [
            'name' => 'Alpha',
        ]);
        $responseGet->assertOk()->assertSee('Alpha Core');

        $responseAccounts = FuelMonitoringServer::actingAs($user)->tool(AccountsTool::class, [
            'name' => 'Alpha',
        ]);
        $responseAccounts->assertOk()->assertSee('Alpha Core');
    }

    public function test_overall_account_performance_tool(): void
    {
        $user = User::factory()->create();
        $type = AssetType::create(['name' => 'Hauler']);
        $asset = Asset::create([
            'fleet_no' => 'HL-100',
            'asset_type_id' => $type->id,
            'tank_capacity' => 250,
            'fuel_type' => 'Diesel',
        ]);

        $account = ChargeableAccount::create([
            'name' => 'Expressway Corridor',
            'classification' => 'Running',
            'status' => 'Active',
        ]);

        $subAccount = SubAccount::create([
            'chargeable_account_id' => $account->id,
            'name' => 'Earthmoving Section',
            'quantity' => 1000,
            'unit' => 'cu.m',
        ]);

        SubAccountBudget::create([
            'sub_account_id' => $subAccount->id,
            'budget_quantity' => 1000,
            'status' => 'Approved',
        ]);

        $order = FuelOrder::create([
            'asset_id' => $asset->id,
            'chargeable_account_id' => $account->id,
            'sub_account_id' => $subAccount->id,
            'status' => 'DONE',
            'actual_quantity' => 200,
            'say_quantity' => 180,
        ]);

        UtilizationEntry::create([
            'asset_id' => $asset->id,
            'chargeable_account_id' => $account->id,
            'sub_account_id' => $subAccount->id,
            'fuel_order_id' => $order->id,
            'date' => '2026-10-15',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'fuel_factor_hr' => 2.0,
            'calculation_type' => 'Timeframe',
            'driver_operator_name' => 'Driver One',
            'reference' => 'REF-EXP-01',
            'particulars' => 'Earthmoving activities',
        ]);

        AccomplishmentRegistry::create([
            'sub_account_id' => $subAccount->id,
            'quantity' => 250,
            'date_at' => '2026-10-16',
            'reference_id' => 'ACC-001',
            'remarks' => 'Quarter milestone',
        ]);

        $response = FuelMonitoringServer::actingAs($user)->tool(OverallAccountPerformanceTool::class, [
            'account_id' => $account->id,
        ]);

        $response->assertOk()
            ->assertSee('Expressway Corridor')
            ->assertSee('Earthmoving Section')
            ->assertSee('Healthy')
            ->assertSee('1000')
            ->assertSee('788');
    }

    public function test_json_rpc_tools_call_for_accounts_and_overall_account_performance(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;
        $account = ChargeableAccount::create([
            'name' => 'Solar Farm Project',
            'classification' => 'Running',
            'status' => 'Active',
        ]);

        $searchRpcResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/mcp', [
                'jsonrpc' => '2.0',
                'method' => 'tools/call',
                'id' => 10,
                'params' => [
                    'name' => 'search_accounts',
                    'arguments' => [
                        'name' => 'Solar',
                    ],
                ],
            ]);

        $searchRpcResponse->assertOk()
            ->assertSee('Solar Farm Project');

        $performanceRpcResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/mcp', [
                'jsonrpc' => '2.0',
                'method' => 'tools/call',
                'id' => 11,
                'params' => [
                    'name' => 'overall_account_performance',
                    'arguments' => [
                        'account_id' => $account->id,
                    ],
                ],
            ]);

        $performanceRpcResponse->assertOk()
            ->assertSee('Solar Farm Project')
            ->assertSee('summary_statistics')
            ->assertSee('projection_summary')
            ->assertSee('sub_account_breakdown');

        $rpcJson = $performanceRpcResponse->json();
        $payloadText = data_get($rpcJson, 'result.content.0.text');
        $this->assertNotNull($payloadText);
        $payload = json_decode($payloadText, true);

        $this->assertArrayHasKey('account', $payload);
        $this->assertArrayHasKey('summary_statistics', $payload);
        $this->assertArrayHasKey('projection_summary', $payload);
        $this->assertArrayHasKey('sub_account_breakdown', $payload);
        $this->assertEquals('Solar Farm Project', $payload['account']['name']);
    }

    public function test_overall_account_performance_not_found(): void
    {
        $user = User::factory()->create();
        $account = ChargeableAccount::create([
            'name' => 'Metro Line 1',
            'classification' => 'Running',
            'status' => 'Active',
        ]);

        $responseOk = FuelMonitoringServer::actingAs($user)->tool(OverallAccountPerformanceTool::class, [
            'account_id' => $account->id,
        ]);
        $responseOk->assertOk()->assertSee('Metro Line 1');

        $responseNotFound = FuelMonitoringServer::actingAs($user)->tool(OverallAccountPerformanceTool::class, [
            'account_id' => 99999,
        ]);
        $responseNotFound->assertSee('not found');
    }
}
