# Fuel Monitoring MCP Server Documentation

A read-only Model Context Protocol (MCP) server built with the official [`laravel/mcp`](https://laravel.com/docs/mcp) package. This server allows AI agents (e.g., Claude Desktop, Cursor, MCP Inspector) to safely inspect and query data from the Fuel Monitoring application without making modifications.

---

## 1. Connection & Authentication

The MCP server is served over HTTP at `/mcp` and is secured with **Laravel Sanctum Bearer Tokens** (`auth:sanctum` middleware).

### Endpoint
- **URL**: `http://<your-host>/mcp`
- **Method**: `POST` (Standard MCP JSON-RPC 2.0)
- **Required Headers**:
  ```http
  Authorization: Bearer <SANCTUM_PERSONAL_ACCESS_TOKEN>
  Accept: application/json
  Content-Type: application/json
  ```

### Generating a Token

#### Option 1: Admin Web Interface (Recommended)
Administrators can generate, inspect, and revoke tokens directly from the system UI:
1. Log in with an **Administrator** account.
2. Navigate to **Settings** &rarr; **MCP Tokens** (`/settings/mcp-tokens`).
3. Enter a label (e.g., `Claude Desktop`, `Cursor AI`) and select the user context.
4. Click **Generate Token** and copy the plaintext token.

#### Option 2: Via Laravel Tinker (CLI)
```bash
php artisan tinker
```
```php
$user = App\Models\User::first();
$token = $user->createToken('ai-agent-token')->plainTextToken;
echo $token;
```

---

## 2. MCP Client Configuration

### Example: Claude Desktop / Cursor (`claude_desktop_config.json` or `mcp.json`)
```json
{
  "mcpServers": {
    "fuel-monitoring": {
      "url": "http://127.0.0.1:8000/mcp",
      "headers": {
        "Authorization": "Bearer 1|yourSanctumPlainTextTokenHere"
      }
    }
  }
}
```

---

## 3. Available MCP Resources

Resources allow AI clients to read structured data via uniform resource identifiers (URIs).

### 1. `assets://list`
- **Description**: Returns all active assets, equipment, and vehicles along with their classifications and latest status.
- **URI**: `assets://list`
- **MIME Type**: `application/json`

### 2. `assets://{asset_id}`
- **Description**: Returns detailed specifications for an individual asset, including tank capacity, fuel factor, fuel type, and current readings.
- **URI Template**: `assets://{asset_id}`
- **Parameters**:
  - `asset_id` *(integer, required)*: The unique ID of the asset.
- **MIME Type**: `application/json`

### 3. `fuel_orders://recent`
- **Description**: Returns the 20 most recent fuel replenishment orders along with their statuses, associated asset, and sub-account details.
- **URI**: `fuel_orders://recent`
- **MIME Type**: `application/json`

### 4. `accounts://budgets`
- **Description**: Returns all active Chargeable Accounts and their associated Sub-Account budget allocations.
- **URI**: `accounts://budgets`
- **MIME Type**: `application/json`

### 5. `dashboard://{account_id}`
- **Description**: Exposes the aggregated sub-account analytics and consumption chart data for a Chargeable Account (identical to the data presented in `/dashboard/account/{id}/sub-accounts`).
- **URI Template**: `dashboard://{account_id}`
- **Parameters**:
  - `account_id` *(integer, required)*: The ID of the Chargeable Account.
- **MIME Type**: `application/json`

---

## 4. Available MCP Tools

All tools are marked with the `#[IsReadOnly]` attribute, guaranteeing that they do not mutate or delete application state.

### 1. `search_assets`
Search for assets by fleet number or plate number with optional filtering.
- **Parameters**:
  - `query` *(string, required)*: The search term matching `fleet_no` or `plate_no`.
  - `asset_type_id` *(integer, optional)*: Filter results by asset type ID.
- **Returns**: A list of matching asset records.

### 2. `get_asset_utilization_logs`
Retrieve historical utilization and fuel consumption entries for an asset.
- **Parameters**:
  - `asset_id` *(integer, required)*: The asset ID.
  - `start_date` *(string, optional)*: Filter entries starting from date (`YYYY-MM-DD`).
  - `end_date` *(string, optional)*: Filter entries up to date (`YYYY-MM-DD`).
  - `limit` *(integer, optional)*: Maximum number of entries to return (default: `50`, max: `200`).
- **Returns**: A list of utilization entries sorted by date descending.

### 3. `get_fuel_order_details`
Retrieve comprehensive details for a single fuel order.
- **Parameters**:
  - `fuel_order_id` *(integer, required)*: The ID of the fuel order.
- **Returns**: The fuel order entity including its asset, chargeable account, sub-account, attached utilization entries, actualization details, and waiver information.

### 4. `get_sub_account_accomplishment`
Inspect milestone progress, target volume, and accomplishment history for a sub-account.
- **Parameters**:
  - `sub_account_id` *(integer, required)*: The ID of the sub-account.
- **Returns**:
  ```json
  {
    "sub_account_id": 1,
    "name": "Sub Account Name",
    "type": "Contract",
    "chargeable_account": {
      "id": 1,
      "name": "Project Alpha"
    },
    "target_quantity": 1000.0,
    "unit": "cu.m",
    "accomplishment_percentage": 25.0,
    "latest_accomplishment": {
      "id": 10,
      "quantity": 250.0,
      "date_at": "2026-10-22",
      "reference_id": "REF-001",
      "remarks": "First milestone reached"
    },
    "history": [ ... ]
  }
  ```

---

## 5. Testing & Debugging

### Run MCP Unit and Feature Tests
Always run with config cache cleared:
```bash
php artisan config:clear --ansi && php artisan test --filter McpServerTest
```

### Interactive MCP Inspector
You can inspect the MCP server interactively:
```bash
php artisan mcp:inspector
```
