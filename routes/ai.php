<?php

use App\Mcp\Servers\FuelMonitoringServer;
use Laravel\Mcp\Facades\Mcp;

Mcp::web('/mcp', FuelMonitoringServer::class)->middleware('auth:sanctum');
