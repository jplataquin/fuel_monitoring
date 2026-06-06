<?php

namespace App\Traits;

use App\Models\ChargeableAccount;
use App\Models\FuelOrder;
use App\Models\SubAccountBudget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

trait DashboardDataTrait
{
    /**
     * Get dashboard data for budget cards.
     */
    protected function getBudgetDashboardData(?string $dateFrom = null, ?string $dateTo = null, ?int $accountId = null): array
    {
        $query = FuelOrder::with(['utilizationEntries.chargeableAccount', 'utilizationEntries.subAccount'])
            ->where('status', 'DONE')
            ->whereHas('utilizationEntries.chargeableAccount', function ($q) {
                $q->where('status', 'Active');
            })
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

        $fuelOrders = $query->get();
        $accountSummaries = [];

        foreach ($fuelOrders as $order) {
            $orderTotalCalcQty = 0;
            $orderActualQty = $order->actual_quantity;
            
            foreach ($order->utilizationEntries as $entry) {
                $calcType = strtolower($entry->calculation_type ?? '');
                $qty = 0;
                
                if (str_contains($calcType, 'kilometer')) {
                    $calcKm = max(0, $entry->end_kilometer_reading - $entry->start_kilometer_reading);
                    $qty = $entry->fuel_factor_km > 0 ? $calcKm / $entry->fuel_factor_km : 0;
                } elseif (str_contains($calcType, 'actual')) {
                    if ($entry->end_time && $entry->start_time) {
                        $start = Carbon::parse($entry->date->format('Y-m-d').' '.$entry->start_time->format('H:i:s'));
                        $end = Carbon::parse($entry->date->format('Y-m-d').' '.$entry->end_time->format('H:i:s'));
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

                if (!isset($accountSummaries[$accountName])) {
                    $totalBudget = 0;
                    $offsetFuel = 0;
                    if ($account) {
                        foreach ($account->subAccounts as $sa) {
                            $totalBudget += SubAccountBudget::where('sub_account_id', $sa->id)
                                ->where('status', 'Approved')
                                ->sum('budget_quantity');
                        }
                        $offsetFuel = $account->offsets()->sum('quantity');
                    }

                    $accountSummaries[$accountName] = [
                        'name' => $accountName,
                        'total_budget' => $totalBudget,
                        'offset_fuel' => $offsetFuel,
                        'actual_fuel' => 0,
                        'budgeted_fuel' => 0,
                        'unbudgeted_fuel' => 0,
                        'total_calculated_fuel' => 0,
                    ];
                }

                $entryCalcQty = $entry->_calculated_qty;
                if ($entry->unbudgeted) {
                    $accountSummaries[$accountName]['unbudgeted_fuel'] += $entryCalcQty;
                } else {
                    $accountSummaries[$accountName]['budgeted_fuel'] += $entryCalcQty;
                }
                $accountSummaries[$accountName]['total_calculated_fuel'] += $entryCalcQty;

                $proratedActual = 0;
                if ($orderTotalCalcQty > 0) {
                    $proratedActual = ($entryCalcQty / $orderTotalCalcQty) * $orderActualQty;
                } else {
                    $proratedActual = $orderActualQty / max(1, $order->utilizationEntries->count());
                }
                $accountSummaries[$accountName]['actual_fuel'] += $proratedActual;
            }
        }

        // Add accounts that have budget but no fuel orders yet in this period
        $allAccountsQuery = ChargeableAccount::with('subAccounts')->where('status', 'Active');
        if ($accountId) {
            $allAccountsQuery->where('id', $accountId);
        }
        $allAccounts = $allAccountsQuery->get();

        foreach ($allAccounts as $account) {
            $accountName = $account->name;
            if (!isset($accountSummaries[$accountName])) {
                $totalBudget = 0;
                $offsetFuel = 0;
                foreach ($account->subAccounts as $sa) {
                    $totalBudget += SubAccountBudget::where('sub_account_id', $sa->id)
                        ->where('status', 'Approved')
                        ->sum('budget_quantity');
                }
                
                $offsetFuel = $account->offsets()->sum('quantity');

                if ($totalBudget > 0 || $offsetFuel > 0) {
                    $accountSummaries[$accountName] = [
                        'name' => $accountName,
                        'total_budget' => $totalBudget,
                        'offset_fuel' => $offsetFuel,
                        'actual_fuel' => 0,
                        'budgeted_fuel' => 0,
                        'unbudgeted_fuel' => 0,
                        'total_calculated_fuel' => 0,
                    ];
                }
            }
        }

        ksort($accountSummaries);

        return array_values($accountSummaries);
    }

    /**
     * Get asset variance data for asset cards.
     */
    protected function getAssetVarianceData(?string $dateFrom = null, ?string $dateTo = null, ?int $assetId = null): Collection
    {
        $query = \App\Models\Asset::with(['fuelOrders' => function($q) use ($dateFrom, $dateTo) {
            $q->where('status', 'DONE');
            
            if ($dateFrom) {
                $q->whereDate('created_at', '>=', $dateFrom);
            }
            
            if ($dateTo) {
                $q->whereDate('created_at', '<=', $dateTo);
            }
        }]);

        if ($assetId) {
            $query->where('id', $assetId);
        }

        $assets = $query->orderBy('fleet_no')->get();

        return $assets->map(function($asset) {
            $totalSay = $asset->fuelOrders->sum('say_quantity');
            $totalActual = $asset->fuelOrders->sum('actual_quantity');
            
            $variancePercent = 0;
            if ($totalSay > 0) {
                $variancePercent = (($totalActual - $totalSay) / $totalSay) * 100;
            }

            return [
                'id' => $asset->id,
                'fleet_no' => $asset->fleet_no,
                'plate_no' => $asset->plate_no,
                'total_say' => $totalSay,
                'total_actual' => $totalActual,
                'variance_percent' => $variancePercent,
                'order_count' => $asset->fuelOrders->count()
            ];
        });
    }
}
