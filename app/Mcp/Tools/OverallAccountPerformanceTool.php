<?php

namespace App\Mcp\Tools;

use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Name('overall_account_performance')]
#[IsReadOnly]
#[Description('Retrieve overall account performance data including Summary Statistics, projection summary, and sub-account breakdown matching the /dashboard/account/{account_id}/sub-accounts view.')]
class OverallAccountPerformanceTool extends OverallAccountPreformanceTool {}
