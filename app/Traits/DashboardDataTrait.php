<?php

namespace App\Traits;

use App\Models\Asset;
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
                if ($entry->trashed() || $entry->fuel_order_id === null) {
                    continue;
                }

                $calcType = strtolower($entry->calculation_type ?? '');
                $qty = 0;

                if (str_contains($calcType, 'kilometer')) {
                    $calcKm = max(0, $entry->end_kilometer_reading - $entry->start_kilometer_reading);
                    $qty = $entry->fuel_factor_km > 0 ? $calcKm / $entry->fuel_factor_km : 0;
                } elseif (str_contains($calcType, 'timeframe')) {
                    if ($entry->end_time && $entry->start_time) {
                        $start = Carbon::parse($entry->date->format('Y-m-d').' '.$entry->start_time->format('H:i:s'));
                        $end = Carbon::parse($entry->date->format('Y-m-d').' '.$entry->end_time->format('H:i:s'));
                        $calcHours = max(0, $start->diffInMinutes($end) / 60);
                        $qty = $calcHours * $entry->fuel_factor_hr;
                    }
                } elseif (str_contains($calcType, 'actual')) {
                    $qty = ($entry->actual_hours ?? 0) * $entry->fuel_factor_hr;
                } elseif (str_contains($calcType, 'hour')) {
                    $calcHours = max(0, $entry->end_hour_reading - $entry->start_hour_reading);
                    $qty = $calcHours * $entry->fuel_factor_hr;
                }

                $entry->_calculated_qty = $qty;
                $orderTotalCalcQty += $qty;
            }

            foreach ($order->utilizationEntries as $entry) {
                if ($entry->trashed() || $entry->fuel_order_id === null) {
                    continue;
                }

                if ($accountId && $entry->chargeable_account_id != $accountId) {
                    continue;
                }

                $account = $entry->chargeableAccount;
                if (! $account || $account->status !== 'Active') {
                    continue;
                }
                $accountName = $account->name ?? 'Unassigned';

                // Automatically filter Scoped accounts by their dates, and Running by user-selected filters
                if ($account->classification === 'Scoped') {
                    $entryDate = $entry->date ? Carbon::parse($entry->date)->startOfDay() : null;
                    $startDate = $account->start_date ? Carbon::parse($account->start_date)->startOfDay() : null;
                    $endDate = $account->end_date ? Carbon::parse($account->end_date)->startOfDay() : null;

                    if ($entryDate) {
                        if ($startDate && $entryDate->lt($startDate)) {
                            continue;
                        }
                        if ($endDate && $entryDate->gt($endDate)) {
                            continue;
                        }
                    }
                } else {
                    $orderDate = Carbon::parse($order->created_at)->startOfDay();
                    if ($dateFrom) {
                        $dateFromBound = Carbon::parse($dateFrom)->startOfDay();
                        if ($orderDate->lt($dateFromBound)) {
                            continue;
                        }
                    }
                    if ($dateTo) {
                        $dateToBound = Carbon::parse($dateTo)->startOfDay();
                        if ($orderDate->gt($dateToBound)) {
                            continue;
                        }
                    }
                }

                if (! isset($accountSummaries[$accountName])) {
                    $totalBudget = 0;
                    if ($account) {
                        foreach ($account->subAccounts as $sa) {
                            $totalBudget += SubAccountBudget::where('sub_account_id', $sa->id)
                                ->where('status', 'Approved')
                                ->sum('budget_quantity');
                        }
                    }

                    $accountSummaries[$accountName] = [
                        'name' => $accountName,
                        'account_id' => $account ? $account->id : null,
                        'classification' => $account ? $account->classification : null,
                        'start_date' => $account && $account->start_date ? $account->start_date->format('Y-m-d') : null,
                        'end_date' => $account && $account->end_date ? $account->end_date->format('Y-m-d') : null,
                        'total_budget' => $totalBudget,
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
            if (! isset($accountSummaries[$accountName])) {
                $totalBudget = 0;
                foreach ($account->subAccounts as $sa) {
                    $totalBudget += SubAccountBudget::where('sub_account_id', $sa->id)
                        ->where('status', 'Approved')
                        ->sum('budget_quantity');
                }

                if ($totalBudget > 0) {
                    $accountSummaries[$accountName] = [
                        'name' => $accountName,
                        'account_id' => $account ? $account->id : null,
                        'classification' => $account ? $account->classification : null,
                        'start_date' => $account && $account->start_date ? $account->start_date->format('Y-m-d') : null,
                        'end_date' => $account && $account->end_date ? $account->end_date->format('Y-m-d') : null,
                        'total_budget' => $totalBudget,
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
        $query = Asset::with(['fuelOrders' => function ($q) use ($dateFrom, $dateTo) {
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

        return $assets->map(function ($asset) {
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
                'order_count' => $asset->fuelOrders->count(),
            ];
        });
    }

    /**
     * Get sub account dashboard data for a chargeable account.
     */
    protected function getSubAccountDashboardData(ChargeableAccount $chargeableAccount): array
    {
        // 1. Get all active sub-accounts
        $subAccounts = $chargeableAccount->subAccounts()->get();

        // 2. Fetch all fuel orders associated with this account (using the exact calculation logic)
        $fuelOrders = FuelOrder::with(['utilizationEntries' => function ($q) use ($chargeableAccount) {
            $q->where('chargeable_account_id', $chargeableAccount->id);
        }])
            ->where('status', 'DONE')
            ->whereHas('utilizationEntries', function ($q) use ($chargeableAccount) {
                $q->where('chargeable_account_id', $chargeableAccount->id);
            })
            ->get();

        // Initialize consumption data
        $subAccountConsumption = [];
        foreach ($subAccounts as $sa) {
            $subAccountConsumption[$sa->id] = 0;
        }

        // Loop over fuel orders and calculate prorated entry calculated quantities
        foreach ($fuelOrders as $order) {
            $orderTotalCalcQty = 0;
            $orderActualQty = $order->actual_quantity;

            // Calculate total calculated qty for the entire order
            foreach ($order->utilizationEntries as $entry) {
                if ($entry->trashed() || $entry->fuel_order_id === null) {
                    continue;
                }

                $calcType = strtolower($entry->calculation_type ?? '');
                $qty = 0;

                if (str_contains($calcType, 'kilometer')) {
                    $calcKm = max(0, $entry->end_kilometer_reading - $entry->start_kilometer_reading);
                    $qty = $entry->fuel_factor_km > 0 ? $calcKm / $entry->fuel_factor_km : 0;
                } elseif (str_contains($calcType, 'timeframe')) {
                    if ($entry->end_time && $entry->start_time) {
                        $start = Carbon::parse($entry->date->format('Y-m-d').' '.$entry->start_time->format('H:i:s'));
                        $end = Carbon::parse($entry->date->format('Y-m-d').' '.$entry->end_time->format('H:i:s'));
                        $calcHours = max(0, $start->diffInMinutes($end) / 60);
                        $qty = $calcHours * $entry->fuel_factor_hr;
                    }
                } elseif (str_contains($calcType, 'actual')) {
                    $qty = ($entry->actual_hours ?? 0) * $entry->fuel_factor_hr;
                } elseif (str_contains($calcType, 'hour')) {
                    $calcHours = max(0, $entry->end_hour_reading - $entry->start_hour_reading);
                    $qty = $calcHours * $entry->fuel_factor_hr;
                }

                $entry->_calculated_qty = $qty;
                $orderTotalCalcQty += $qty;
            }

            // Accumulate calculated qty per sub-account
            foreach ($order->utilizationEntries as $entry) {
                if ($entry->trashed() || $entry->fuel_order_id === null) {
                    continue;
                }

                if ($entry->chargeable_account_id != $chargeableAccount->id || ! $entry->sub_account_id) {
                    continue;
                }

                // If scoped, apply scoped date boundaries
                if ($chargeableAccount->classification === 'Scoped') {
                    $entryDate = $entry->date ? Carbon::parse($entry->date)->startOfDay() : null;
                    $startDate = $chargeableAccount->start_date ? Carbon::parse($chargeableAccount->start_date)->startOfDay() : null;
                    $endDate = $chargeableAccount->end_date ? Carbon::parse($chargeableAccount->end_date)->startOfDay() : null;

                    if ($entryDate) {
                        if ($startDate && $entryDate->lt($startDate)) {
                            continue;
                        }
                        if ($endDate && $entryDate->gt($endDate)) {
                            continue;
                        }
                    }
                }

                if (isset($subAccountConsumption[$entry->sub_account_id])) {
                    $subAccountConsumption[$entry->sub_account_id] += $entry->_calculated_qty;
                }
            }
        }

        // 3. Prepare Chart Data (Sub-Account name, Approved Budget, Consumed calculated, Remaining Balance)
        $chartLabels = [];
        $remainingBalances = [];
        $subAccountData = [];

        foreach ($subAccounts as $sa) {
            $totalBudget = SubAccountBudget::where('sub_account_id', $sa->id)
                ->where('status', 'Approved')
                ->sum('budget_quantity');

            $consumed = $subAccountConsumption[$sa->id] ?? 0;
            $remaining = $totalBudget - $consumed;

            $chartLabels[] = $sa->name;
            $remainingBalances[] = round($remaining, 2);

            $subAccountData[] = [
                'id' => $sa->id,
                'name' => $sa->name,
                'total_budget' => $totalBudget,
                'consumed' => $consumed,
                'remaining' => $remaining,
                'accomplishment' => $sa->accomplishment,
                'quantity' => $sa->quantity,
                'unit' => $sa->unit,
            ];
        }

        $totalActualQuantity = $fuelOrders->sum('actual_quantity');

        return [
            'chartLabels' => $chartLabels,
            'remainingBalances' => $remainingBalances,
            'subAccountData' => $subAccountData,
            'totalActualQuantity' => $totalActualQuantity,
        ];
    }
}
