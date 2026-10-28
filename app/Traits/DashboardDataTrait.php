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

        // 2. Fetch all fuel orders associated with this account (loading all entries for prorating)
        $fuelOrders = FuelOrder::with('utilizationEntries')
            ->where('status', 'DONE')
            ->whereHas('utilizationEntries', function ($q) use ($chargeableAccount) {
                $q->where('chargeable_account_id', $chargeableAccount->id);
            })
            ->get();

        // Initialize consumption data
        $subAccountConsumption = [];
        $subAccountActualConsumption = [];
        foreach ($subAccounts as $sa) {
            $subAccountConsumption[$sa->id] = 0;
            $subAccountActualConsumption[$sa->id] = 0;
        }

        $totalActualQuantity = 0;
        $totalCalculatedQuantity = 0;

        // Loop over fuel orders and calculate prorated entry calculated quantities in a single pass
        foreach ($fuelOrders as $order) {
            $orderTotalCalcQty = 0;
            $projectCalcQty = 0;
            $orderSubAccountCalcQty = [];

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

                // Process project-specific entries and update sub-account consumption
                if ($entry->chargeable_account_id == $chargeableAccount->id) {
                    // If scoped, apply scoped date boundaries
                    $dateMatches = true;
                    if ($chargeableAccount->classification === 'Scoped') {
                        $entryDate = $entry->date ? Carbon::parse($entry->date)->startOfDay() : null;
                        $startDate = $chargeableAccount->start_date ? Carbon::parse($chargeableAccount->start_date)->startOfDay() : null;
                        $endDate = $chargeableAccount->end_date ? Carbon::parse($chargeableAccount->end_date)->startOfDay() : null;

                        if ($entryDate) {
                            if ($startDate && $entryDate->lt($startDate)) {
                                $dateMatches = false;
                            }
                            if ($endDate && $entryDate->gt($endDate)) {
                                $dateMatches = false;
                            }
                        }
                    }

                    if ($dateMatches) {
                        $projectCalcQty += $qty;
                        $totalCalculatedQuantity += $qty;

                        if ($entry->sub_account_id && isset($subAccountConsumption[$entry->sub_account_id])) {
                            $subAccountConsumption[$entry->sub_account_id] += $qty;
                            $orderSubAccountCalcQty[$entry->sub_account_id] = ($orderSubAccountCalcQty[$entry->sub_account_id] ?? 0) + $qty;
                        }
                    }
                }
            }

            // Prorate actual quantity based on project weighted percentage of computed fuel
            if ($orderTotalCalcQty > 0) {
                if ($projectCalcQty > 0) {
                    $weightedPercentage = $projectCalcQty / $orderTotalCalcQty;
                    $totalActualQuantity += $order->actual_quantity * $weightedPercentage;
                }

                foreach ($orderSubAccountCalcQty as $subAccountId => $saCalcQty) {
                    if ($saCalcQty > 0) {
                        $saWeightedPercentage = $saCalcQty / $orderTotalCalcQty;
                        $subAccountActualConsumption[$subAccountId] += $order->actual_quantity * $saWeightedPercentage;
                    }
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
            $actualConsumed = $subAccountActualConsumption[$sa->id] ?? 0;
            $remaining = $totalBudget - $actualConsumed;

            $chartLabels[] = $sa->name;
            $remainingBalances[] = round($remaining, 2);

            $latestAccomplishment = $sa->accomplishments()->latest('date_at')->latest('id')->first();
            $accomplishedQty = $latestAccomplishment ? $latestAccomplishment->quantity : null;

            $fuelUsedPercentage = $totalBudget > 0 ? ($actualConsumed / $totalBudget) * 100 : ($actualConsumed > 0 ? 100.0 : 0.0);

            $subAccountData[] = [
                'id' => $sa->id,
                'name' => $sa->name,
                'total_budget' => $totalBudget,
                'consumed' => $consumed,
                'actual_consumed' => $actualConsumed,
                'remaining' => $remaining,
                'accomplishment' => $sa->accomplishment,
                'fuel_used_percentage' => round($fuelUsedPercentage, 2),
                'quantity' => $sa->quantity,
                'unit' => $sa->unit,
                'accomplished_qty' => $accomplishedQty,
            ];
        }

        return [
            'chartLabels' => $chartLabels,
            'remainingBalances' => $remainingBalances,
            'subAccountData' => $subAccountData,
            'totalActualQuantity' => $totalActualQuantity,
            'totalCalculatedQuantity' => $totalCalculatedQuantity,
        ];
    }

    /**
     * Compute comprehensive overall account performance metrics matching the sub-accounts dashboard view.
     */
    public function getOverallAccountPerformanceData(ChargeableAccount $chargeableAccount): array
    {
        $dashboardData = $this->getSubAccountDashboardData($chargeableAccount);
        $subAccountData = $dashboardData['subAccountData'];
        $totalActualQuantity = $dashboardData['totalActualQuantity'];
        $totalCalculatedQuantity = $dashboardData['totalCalculatedQuantity'];

        $totalBudgets = (float) collect($subAccountData)->sum('total_budget');
        $totalConsumed = (float) ($totalCalculatedQuantity ?? collect($subAccountData)->sum('consumed'));
        $totalActual = (float) ($totalActualQuantity ?? collect($subAccountData)->sum('actual_consumed'));
        $totalRemaining = (float) ($totalBudgets - ($totalActualQuantity ?? 0));
        $utilizationPercent = $totalBudgets > 0 ? ($totalActual / $totalBudgets) * 100 : 0;

        $totalSavings = 0;
        $totalExcess = 0;
        $subAccountBreakdown = [];

        foreach ($subAccountData as $sa) {
            $saPercent = $sa['fuel_used_percentage'] ?? ($sa['total_budget'] > 0 ? ($sa['actual_consumed'] / $sa['total_budget']) * 100 : ($sa['actual_consumed'] > 0 ? 100.0 : 0.0));

            $saStatus = 'Healthy';
            if ($saPercent >= 100) {
                $saStatus = 'Exhausted';
            } elseif ($saPercent >= 90) {
                $saStatus = 'Critical';
            } elseif ($saPercent >= 75) {
                $saStatus = 'Warning';
            }

            $rateVal = $sa['consumed'] > 0 && $sa['accomplished_qty'] !== null
                ? ceil(($sa['accomplished_qty'] / $sa['consumed']) * 100) / 100
                : null;

            $projectedVal = $rateVal > 0 && $sa['quantity'] !== null && $sa['accomplished_qty'] !== null
                ? ($sa['quantity'] - $sa['accomplished_qty']) / $rateVal
                : null;

            $optimalVal = $sa['remaining'] > 0 && $sa['quantity'] !== null && $sa['accomplished_qty'] !== null
                ? ($sa['quantity'] - $sa['accomplished_qty']) / $sa['remaining']
                : null;

            $remainingQty = $sa['quantity'] !== null && $sa['accomplished_qty'] !== null
                ? $sa['quantity'] - $sa['accomplished_qty']
                : null;

            $varianceVal = $projectedVal !== null
                ? $sa['remaining'] - $projectedVal
                : ($rateVal === null && $sa['remaining'] < 0 ? $sa['remaining'] : null);

            if ($varianceVal !== null) {
                if ($varianceVal > 0) {
                    $totalSavings += $varianceVal;
                } elseif ($varianceVal < 0) {
                    $totalExcess += abs($varianceVal);
                }
            }

            $subAccountBreakdown[] = [
                'id' => $sa['id'],
                'name' => $sa['name'],
                'total_budget' => round((float) $sa['total_budget'], 2),
                'calculated_quantity' => round((float) $sa['consumed'], 2),
                'actual_quantity' => round((float) ($sa['actual_consumed'] ?? 0), 2),
                'remaining_balance' => round((float) $sa['remaining'], 2),
                'target_quantity' => $sa['quantity'] !== null ? round((float) $sa['quantity'], 2) : null,
                'accomplished_quantity' => $sa['accomplished_qty'] !== null ? round((float) $sa['accomplished_qty'], 2) : null,
                'remaining_quantity' => $remainingQty !== null ? round((float) $remainingQty, 2) : null,
                'unit' => $sa['unit'],
                'rate' => $rateVal !== null ? round((float) $rateVal, 2) : null,
                'projection' => $projectedVal !== null ? round((float) $projectedVal, 2) : null,
                'projected_variance' => $varianceVal !== null ? round((float) $varianceVal, 2) : null,
                'optimal_rate' => $optimalVal !== null ? round((float) $optimalVal, 2) : null,
                'accomplishment_percentage' => round((float) ($sa['accomplishment'] ?? 0.0), 2),
                'fuel_used_percentage' => round((float) $saPercent, 2),
                'utilization_status' => $saStatus,
            ];
        }

        return [
            'account' => [
                'id' => $chargeableAccount->id,
                'name' => $chargeableAccount->name,
                'classification' => $chargeableAccount->classification,
                'status' => $chargeableAccount->status,
                'start_date' => $chargeableAccount->start_date ? $chargeableAccount->start_date->format('Y-m-d') : null,
                'end_date' => $chargeableAccount->end_date ? $chargeableAccount->end_date->format('Y-m-d') : null,
            ],
            'summary_statistics' => [
                'total_allocated_budget' => round($totalBudgets, 2),
                'total_calculated_quantity' => round($totalConsumed, 2),
                'total_actual_quantity' => round($totalActual, 2),
                'total_remaining_balance' => round($totalRemaining, 2),
                'overall_budget_utilization_percentage' => round($utilizationPercent, 2),
            ],
            'projection_summary' => [
                'savings' => round((float) $totalSavings, 2),
                'excess' => round((float) $totalExcess, 2),
                'net' => round((float) ($totalSavings - $totalExcess), 2),
            ],
            'sub_account_breakdown' => $subAccountBreakdown,
            'chart_labels' => $dashboardData['chartLabels'],
            'remaining_balances' => $dashboardData['remainingBalances'],
        ];
    }
}
