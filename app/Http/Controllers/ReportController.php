<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\UtilizationEntry;
use App\Models\SubAccountBudget;
use App\Models\ChargeableAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
    public function assetUtilization(Request $request)
    {
        $assets = Asset::with('assetType')->orderBy('fleet_no')->get();
        
        $query = UtilizationEntry::with(['asset', 'chargeableAccount', 'subAccount', 'fuelOrder'])
            ->whereHas('fuelOrder', function ($q) {
                $q->where('status', 'DONE');
            });

        $assetId = $request->input('asset_id');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        if ($assetId) {
            $query->where('asset_id', $assetId);
        }

        if ($dateFrom) {
            $query->whereDate('date', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('date', '<=', $dateTo);
        }

        $entries = collect();
        if ($assetId || $dateFrom || $dateTo) {
            $entries = $query->orderBy('date', 'asc')
                ->get()
                ->groupBy('fuel_order_id');
        }

        return view('reports.asset-utilization', compact('assets', 'entries', 'assetId', 'dateFrom', 'dateTo'));
    }

    public function fuelOrdersSummary(Request $request)
    {
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $query = \App\Models\FuelOrder::with('asset.assetType')
            ->where('status', 'DONE')
            ->orderBy('created_at', 'asc'); // Order ASC for chart timeline

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $fuelOrders = collect();
        if ($dateFrom || $dateTo) {
            $fuelOrders = $query->get();
        }

        // Prepare chart data
        $chartData = [
            'labels' => [],
            'actual' => [],
            'say' => [],
            'trend' => []
        ];

        if ($fuelOrders->isNotEmpty()) {
            $dailyConsumption = [];
            $dailySayConsumption = [];
            
            // Group by date to get actual and say consumption
            foreach ($fuelOrders as $order) {
                $dateString = \Carbon\Carbon::parse($order->created_at)->format('Y-m-d');
                if (!isset($dailyConsumption[$dateString])) {
                    $dailyConsumption[$dateString] = 0;
                    $dailySayConsumption[$dateString] = 0;
                }
                $dailyConsumption[$dateString] += $order->actual_quantity;
                $dailySayConsumption[$dateString] += $order->say_quantity;
            }
            
            ksort($dailyConsumption); // Ensure chronological order
            ksort($dailySayConsumption);

            $labels = array_keys($dailyConsumption);
            $actualData = array_values($dailyConsumption);
            $sayData = array_values($dailySayConsumption);
            
            // Calculate a simple trend line (Linear Regression)
            $n = count($actualData);
            $sumX = 0;
            $sumY = 0;
            $sumXY = 0;
            $sumXX = 0;

            for ($i = 0; $i < $n; $i++) {
                $sumX += $i;
                $sumY += $actualData[$i];
                $sumXY += ($i * $actualData[$i]);
                $sumXX += ($i * $i);
            }

            $slope = 0;
            $intercept = 0;
            
            if ($n > 1 && ($n * $sumXX - $sumX * $sumX) > 0) {
                $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumXX - $sumX * $sumX);
                $intercept = ($sumY - $slope * $sumX) / $n;
            } elseif ($n > 0) {
                $intercept = $sumY / $n;
            }

            $trendData = [];
            for ($i = 0; $i < $n; $i++) {
                $trendData[] = round($slope * $i + $intercept, 2);
            }
            
            // Project into the future (e.g., next 3 days)
            if ($n > 0) {
                $lastDate = \Carbon\Carbon::parse(end($labels));
                for ($j = 1; $j <= 3; $j++) {
                    $nextDate = $lastDate->copy()->addDays($j)->format('Y-m-d');
                    $labels[] = $nextDate;
                    $actualData[] = null; // No actual data yet
                    $sayData[] = null; // No say data yet
                    $trendData[] = round($slope * ($n - 1 + $j) + $intercept, 2);
                }
            }

            // Format labels for display
            foreach ($labels as &$label) {
                $label = \Carbon\Carbon::parse($label)->format('M d');
            }

            $chartData = [
                'labels' => $labels,
                'actual' => $actualData,
                'say' => $sayData,
                'trend' => $trendData
            ];
        }

        // We re-sort by desc for the table below if needed, but in Laravel we can just reverse the collection
        $fuelOrders = $fuelOrders->sortByDesc('created_at')->values();

        return view('reports.fuel-orders-summary', compact('fuelOrders', 'dateFrom', 'dateTo', 'chartData'));
    }

    public function chargeableAccountSummary(Request $request)
    {
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $accountId = $request->input('account_id');

        $query = \App\Models\FuelOrder::with(['utilizationEntries.chargeableAccount'])
            ->where('status', 'DONE')
            ->orderBy('created_at', 'desc');

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        if ($accountId) {
            $query->whereHas('utilizationEntries', function ($q) use ($accountId) {
                $q->where('chargeable_account_id', $accountId);
            });
        }

        $fuelOrders = collect();
        if ($dateFrom || $dateTo || $accountId) {
            $fuelOrders = $query->get();
        }

        $accountSummaries = [];

        foreach ($fuelOrders as $order) {
            $orderTotalCalcQty = 0;
            $orderActualQty = $order->actual_quantity;
            
            foreach ($order->utilizationEntries as $entry) {
                // ... existing calc logic ...
                $calcType = strtolower($entry->calculation_type ?? '');
                $qty = 0;
                
                if (str_contains($calcType, 'kilometer')) {
                    $calcKm = max(0, $entry->end_kilometer_reading - $entry->start_kilometer_reading);
                    $qty = $entry->fuel_factor_km > 0 ? $calcKm / $entry->fuel_factor_km : 0;
                } elseif (str_contains($calcType, 'actual')) {
                    if ($entry->end_time && $entry->start_time) {
                        $start = \Carbon\Carbon::parse($entry->date->format('Y-m-d').' '.$entry->start_time->format('H:i:s'));
                        $end = \Carbon\Carbon::parse($entry->date->format('Y-m-d').' '.$entry->end_time->format('H:i:s'));
                        $calcHours = max(0, $start->diffInMinutes($end) / 60);
                        $qty = $calcHours * $entry->fuel_factor_hr;
                    }
                } elseif (str_contains($calcType, 'hour')) {
                    $calcHours = max(0, $entry->end_hour_reading - $entry->start_hour_reading);
                    $qty = $calcHours * $entry->fuel_factor_hr;
                }
                
                $entry->_calculated_qty = $qty;
                $orderTotalCalcQty += $qty;
            }

            foreach ($order->utilizationEntries as $entry) {
                if ($accountId && $entry->chargeable_account_id != $accountId) {
                    continue;
                }

                $account = $entry->chargeableAccount;
                $accountName = $account->name ?? 'Unassigned';
                $subAccount = $entry->subAccount;
                $subAccountName = $subAccount->name ?? 'No Sub-Account';

                if (!isset($accountSummaries[$accountName])) {
                    // Fetch the sum of all approved budgets for all sub-accounts of this chargeable account
                    $totalBudget = 0;
                    if ($account) {
                        foreach ($account->subAccounts as $sa) {
                            $sumSubBudget = \App\Models\SubAccountBudget::where('sub_account_id', $sa->id)
                                ->where('status', 'Approved')
                                ->sum('budget_quantity');
                            $totalBudget += $sumSubBudget;
                        }
                    }

                    $accountSummaries[$accountName] = [
                        'name' => $accountName,
                        'total_budget' => $totalBudget,
                        'total_km' => 0,
                        'total_hours' => 0,
                        'budgeted_fuel' => 0,
                        'unbudgeted_fuel' => 0,
                        'total_calculated_fuel' => 0,
                        'actual_fuel' => 0,
                        'sub_accounts' => [],
                    ];
                }

                if (!isset($accountSummaries[$accountName]['sub_accounts'][$subAccountName])) {
                    $subAccountBudget = 0;
                    if ($subAccount) {
                         $subAccountBudget = \App\Models\SubAccountBudget::where('sub_account_id', $subAccount->id)
                                ->where('status', 'Approved')
                                ->sum('budget_quantity');
                    }

                    $accountSummaries[$accountName]['sub_accounts'][$subAccountName] = [
                        'name' => $subAccountName,
                        'total_budget' => $subAccountBudget,
                        'total_km' => 0,
                        'total_hours' => 0,
                        'budgeted_fuel' => 0,
                        'unbudgeted_fuel' => 0,
                        'total_calculated_fuel' => 0,
                        'actual_fuel' => 0,
                    ];
                }

                // ... rest of loop ...

                $calcType = strtolower($entry->calculation_type ?? '');
                $calcKm = 0;
                $calcHours = 0;

                if (str_contains($calcType, 'kilometer')) {
                    $calcKm = max(0, $entry->end_kilometer_reading - $entry->start_kilometer_reading);
                } elseif (str_contains($calcType, 'actual')) {
                    if ($entry->end_time && $entry->start_time) {
                        $start = \Carbon\Carbon::parse($entry->date->format('Y-m-d').' '.$entry->start_time->format('H:i:s'));
                        $end = \Carbon\Carbon::parse($entry->date->format('Y-m-d').' '.$entry->end_time->format('H:i:s'));
                        $calcHours = max(0, $start->diffInMinutes($end) / 60);
                    }
                } elseif (str_contains($calcType, 'hour')) {
                    $calcHours = max(0, $entry->end_hour_reading - $entry->start_hour_reading);
                }

                $accountSummaries[$accountName]['total_km'] += $calcKm;
                $accountSummaries[$accountName]['total_hours'] += $calcHours;
                $accountSummaries[$accountName]['sub_accounts'][$subAccountName]['total_km'] += $calcKm;
                $accountSummaries[$accountName]['sub_accounts'][$subAccountName]['total_hours'] += $calcHours;
                
                $entryCalcQty = $entry->_calculated_qty;
                if ($entry->unbudgeted) {
                    $accountSummaries[$accountName]['unbudgeted_fuel'] += $entryCalcQty;
                    $accountSummaries[$accountName]['sub_accounts'][$subAccountName]['unbudgeted_fuel'] += $entryCalcQty;
                } else {
                    $accountSummaries[$accountName]['budgeted_fuel'] += $entryCalcQty;
                    $accountSummaries[$accountName]['sub_accounts'][$subAccountName]['budgeted_fuel'] += $entryCalcQty;
                }
                $accountSummaries[$accountName]['total_calculated_fuel'] += $entryCalcQty;
                $accountSummaries[$accountName]['sub_accounts'][$subAccountName]['total_calculated_fuel'] += $entryCalcQty;

                $proratedActual = 0;
                if ($orderTotalCalcQty > 0) {
                    $proratedActual = ($entryCalcQty / $orderTotalCalcQty) * $orderActualQty;
                } else {
                    $proratedActual = $orderActualQty / max(1, $order->utilizationEntries->count());
                }
                $accountSummaries[$accountName]['actual_fuel'] += $proratedActual;
                $accountSummaries[$accountName]['sub_accounts'][$subAccountName]['actual_fuel'] += $proratedActual;
            }
        }

        ksort($accountSummaries);

        $accounts = \App\Models\ChargeableAccount::orderBy('name')->get();

        return view('reports.chargeable-account-summary', compact('accountSummaries', 'dateFrom', 'dateTo', 'accounts', 'accountId'));
    }
}
