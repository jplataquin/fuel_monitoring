<?php

namespace App\Mcp\Tools;

use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Name('get_accounts')]
#[IsReadOnly]
#[Description('Retrieve or filter Chargeable Accounts by name (partial match), ID, or status.')]
class GetAccountsTool extends SearchAccountsTool {}
