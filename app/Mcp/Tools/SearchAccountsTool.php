<?php

namespace App\Mcp\Tools;

use App\Models\ChargeableAccount;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Name('search_accounts')]
#[IsReadOnly]
#[Description('Search or filter Chargeable Accounts by name (partial match), ID, or status.')]
class SearchAccountsTool extends Tool
{
    /**
     * Get the tool's input schema.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('Optional search term to filter accounts by name (partial match)'),
            'id' => $schema->integer()->description('Optional account ID filter'),
            'status' => $schema->string()->description('Optional status filter (e.g., "Active", "Inactive")'),
        ];
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $request->validate([
            'name' => ['nullable', 'string'],
            'query' => ['nullable', 'string'],
            'search' => ['nullable', 'string'],
            'account_name' => ['nullable', 'string'],
            'id' => ['nullable', 'integer'],
            'account_id' => ['nullable', 'integer'],
            'status' => ['nullable', 'string'],
        ]);

        $name = $request->get('name') ?? $request->get('query') ?? $request->get('search') ?? $request->get('account_name');
        $id = $request->get('id') ?? $request->get('account_id');
        $status = $request->get('status');

        $query = ChargeableAccount::with('subAccounts');

        if ($id !== null) {
            $query->where('id', $id);
        }

        if ($name !== null && trim($name) !== '') {
            $searchTerm = trim($name);
            $query->where('name', 'like', "%{$searchTerm}%");
        }

        if ($status !== null && trim($status) !== '') {
            $query->where('status', trim($status));
        }

        $accounts = $query->orderBy('name')->get();

        return Response::json($accounts);
    }
}
