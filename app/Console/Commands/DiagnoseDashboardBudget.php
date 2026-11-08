<?php

namespace App\Console\Commands;

use App\Models\ChargeableAccount;
use App\Models\FuelOrder;
use App\Models\SubAccount;
use App\Traits\DashboardDataTrait;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class DiagnoseDashboardBudget extends Command
{
    use DashboardDataTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dashboard:diagnose {account? : Optional Chargeable account ID or name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Diagnose discrepancies between /dashboard card (Budgeted Consumed) and /dashboard/account/{id}/sub-accounts (Total Actual Quantity)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $accountInput = $this->argument('account');

        $query = ChargeableAccount::with('subAccounts');
        if ($accountInput) {
            if (is_numeric($accountInput)) {
                $query->where('id', $accountInput);
            } else {
                $query->where('name', 'like', "%{$accountInput}%");
            }
        } else {
            $query->where('status', 'Active');
        }

        $accounts = $query->orderBy('name')->get();

        if ($accounts->isEmpty()) {
            $this->error('No matching chargeable accounts found.');

            return 1;
        }

        $allDashboardCards = collect($this->getBudgetDashboardData());

        $this->info('================================================================================');
        $this->info('                 DASHBOARD BUDGET VS SUB-ACCOUNTS DIAGNOSTIC                    ');
        $this->info('================================================================================');

        foreach ($accounts as $account) {
            $this->line('');
            $this->info(">>> Chargeable Account: [ID: {$account->id}] {$account->name} (Classification: {$account->classification}, Status: {$account->status})");

            $card = $allDashboardCards->firstWhere('account_id', $account->id);
            if (! $card) {
                // Try finding by name fallback
                $card = $allDashboardCards->firstWhere('name', $account->name);
            }

            $subData = $this->getSubAccountDashboardData($account);

            $dashboardBudgeted = (float) ($card['budgeted_fuel'] ?? 0);
            $dashboardUnbudgeted = (float) ($card['unbudgeted_fuel'] ?? 0);
            $dashboardActualTotal = (float) ($card['actual_fuel'] ?? 0);
            $dashboardTotalBudget = (float) ($card['total_budget'] ?? 0);
            $dashboardRemaining = max(0, $dashboardTotalBudget - ($dashboardBudgeted + $dashboardUnbudgeted));

            $subTotalActual = (float) ($subData['totalActualQuantity'] ?? 0);
            $subTotalCalculated = (float) ($subData['totalCalculatedQuantity'] ?? 0);
            $subAccountData = $subData['subAccountData'] ?? [];
            $subTotalBudget = (float) collect($subAccountData)->sum('total_budget');
            $subTotalRemaining = $subTotalBudget - $subTotalActual;

            $diffBudgeted = round($dashboardBudgeted - $subTotalActual, 4);
            $isMatch = abs($diffBudgeted) < 0.001;

            $this->table(
                ['Metric', '/dashboard Card', 'Sub-Accounts Summary Statistics', 'Difference / Status'],
                [
                    ['Total Budget', number_format($dashboardTotalBudget, 2).' L', number_format($subTotalBudget, 2).' L', abs($dashboardTotalBudget - $subTotalBudget) < 0.001 ? 'MATCH' : 'MISMATCH ('.number_format($dashboardTotalBudget - $subTotalBudget, 2).' L)'],
                    ['Budget Consumed', number_format($dashboardBudgeted, 2).' L (Budgeted)', number_format($subTotalActual, 2).' L (Total Actual)', $isMatch ? 'MATCH' : 'MISMATCH ('.number_format($diffBudgeted, 2).' L)'],
                    ['Unbudgeted Consumed', number_format($dashboardUnbudgeted, 2).' L', 'N/A', '-'],
                    ['Total Consumed (Actual)', number_format($dashboardActualTotal, 2).' L', number_format($subTotalActual, 2).' L', abs($dashboardActualTotal - $subTotalActual) < 0.001 ? 'MATCH' : 'MISMATCH ('.number_format($dashboardActualTotal - $subTotalActual, 2).' L)'],
                    ['Remaining Balance', number_format($dashboardRemaining, 2).' L', number_format($subTotalRemaining, 2).' L', abs($dashboardRemaining - $subTotalRemaining) < 0.001 ? 'MATCH' : 'MISMATCH ('.number_format($dashboardRemaining - $subTotalRemaining, 2).' L)'],
                ]
            );

            // Sub-Accounts Table
            $this->comment("  Sub-Accounts of [{$account->name}]:");
            $subAccountRows = [];
            $activeSubIds = [];
            foreach ($subAccountData as $sa) {
                $activeSubIds[] = $sa['id'];
                $subAccountRows[] = [
                    $sa['id'],
                    $sa['name'],
                    number_format($sa['total_budget'], 2).' L',
                    number_format($sa['consumed'], 2).' L',
                    number_format($sa['actual_consumed'], 2).' L',
                    number_format($sa['remaining'], 2).' L',
                ];
            }
            $this->table(['Sub ID', 'Sub-Account Name', 'Allocated Budget', 'Calculated Consumed', 'Actual Consumed', 'Remaining'], $subAccountRows);

            // Fetch Fuel Orders associated with this account
            $assetOrders = FuelOrder::with('utilizationEntries')
                ->where('status', 'DONE')
                ->whereHas('utilizationEntries', function ($q) use ($account) {
                    $q->where('chargeable_account_id', $account->id);
                })
                ->get();

            $directOrders = FuelOrder::whereNull('asset_id')
                ->where('status', 'DONE')
                ->where(function ($q) use ($account) {
                    $q->where('chargeable_account_id', $account->id)
                        ->orWhereHas('subAccount', function ($sq) use ($account) {
                            $sq->where('chargeable_account_id', $account->id);
                        });
                })
                ->get();

            $this->comment('  Associated Fuel Orders (DONE):');
            $orderRows = [];
            foreach ($assetOrders as $order) {
                $orderTotalCalcQty = 0;
                $projectCalcQty = 0;
                foreach ($order->utilizationEntries as $e) {
                    if (! $e->trashed() && $e->fuel_order_id) {
                        $orderTotalCalcQty += (float) $e->calculated_quantity;
                        if ($e->chargeable_account_id == $account->id) {
                            $projectCalcQty += (float) $e->calculated_quantity;
                        }
                    }
                }
                $proratedProjectActual = $orderTotalCalcQty > 0 ? ($projectCalcQty / $orderTotalCalcQty) * (float) $order->actual_quantity : 0;
                $orderRows[] = [
                    '#'.$order->id,
                    'Asset Order',
                    $order->created_at->format('Y-m-d H:i'),
                    number_format($order->actual_quantity, 2).' L',
                    number_format($order->calculated_quantity, 2).' L',
                    number_format($projectCalcQty, 2).' L',
                    number_format($proratedProjectActual, 2).' L',
                ];
            }

            foreach ($directOrders as $dOrder) {
                $orderRows[] = [
                    '#'.$dOrder->id,
                    'Direct Order (Sub ID: '.($dOrder->sub_account_id ?: 'None').')',
                    $dOrder->created_at->format('Y-m-d H:i'),
                    number_format($dOrder->actual_quantity, 2).' L',
                    '-',
                    number_format($dOrder->actual_quantity, 2).' L',
                    number_format($dOrder->actual_quantity, 2).' L',
                ];
            }

            if (empty($orderRows)) {
                $this->line('    (No DONE fuel orders found)');
            } else {
                $this->table(['Order ID', 'Type', 'Date', 'Order Actual Qty', 'Order Calc Qty', 'Account Calc Qty', 'Account Prorated Actual'], $orderRows);
            }

            // Entries breakdown
            $this->comment('  Associated Utilization Entries Breakdown:');
            $entryRows = [];
            foreach ($assetOrders as $order) {
                $orderTotalCalcQty = 0;
                foreach ($order->utilizationEntries as $e) {
                    if (! $e->trashed() && $e->fuel_order_id) {
                        $orderTotalCalcQty += (float) $e->calculated_quantity;
                    }
                }

                foreach ($order->utilizationEntries as $e) {
                    if ($e->chargeable_account_id != $account->id) {
                        continue;
                    }

                    $calcQty = (float) $e->calculated_quantity;
                    $prorated = $orderTotalCalcQty > 0 ? ($calcQty / $orderTotalCalcQty) * (float) $order->actual_quantity : 0;
                    $subModel = $e->sub_account_id ? SubAccount::withTrashed()->find($e->sub_account_id) : null;
                    $subName = $subModel ? ($subModel->name.($subModel->trashed() ? ' [DELETED]' : '')) : 'None';
                    $hasValidSub = $e->sub_account_id && in_array($e->sub_account_id, $activeSubIds);

                    // Scoped check
                    $scopedMatch = true;
                    if ($account->classification === 'Scoped') {
                        $eDate = $e->date ? Carbon::parse($e->date)->startOfDay() : null;
                        $sDate = $account->start_date ? Carbon::parse($account->start_date)->startOfDay() : null;
                        $eEndDate = $account->end_date ? Carbon::parse($account->end_date)->startOfDay() : null;
                        if ($eDate) {
                            if ($sDate && $eDate->lt($sDate)) {
                                $scopedMatch = false;
                            }
                            if ($eEndDate && $eDate->gt($eEndDate)) {
                                $scopedMatch = false;
                            }
                        }
                    }

                    $isSoftDeletedSub = $e->sub_account_id && ! in_array($e->sub_account_id, $activeSubIds);
                    if ($isSoftDeletedSub) {
                        $dashboardCategory = 'IGNORED (Deleted Sub)';
                        $subAccountIncluded = 'IGNORED (Deleted Sub)';
                    } else {
                        $dashboardCategory = $e->sub_account_id ? 'Budgeted' : 'Unbudgeted';
                        $subAccountIncluded = ($hasValidSub && $scopedMatch) ? 'YES' : 'SKIPPED ('.(! $hasValidSub ? 'Sub not active/found' : 'Scoped date mismatch').')';
                    }

                    $entryRows[] = [
                        '#'.$e->id,
                        'Order #'.$order->id,
                        $e->date ? $e->date->format('Y-m-d') : '-',
                        $subName.' (ID: '.($e->sub_account_id ?: 'None').')',
                        $e->unbudgeted ? 'YES' : 'NO',
                        number_format($calcQty, 2).' L',
                        number_format($prorated, 2).' L',
                        $dashboardCategory,
                        $subAccountIncluded,
                    ];
                }
            }

            if (empty($entryRows)) {
                $this->line('    (No utilization entries found)');
            } else {
                $this->table(
                    ['Entry ID', 'Order', 'Date', 'Sub-Account', 'Unbudgeted?', 'Calc Qty', 'Prorated Actual', 'Dashboard Cat', 'Sub-Account Dashboard Status'],
                    $entryRows
                );
            }
        }

        $this->line('');
        $this->info('Diagnostic complete.');

        return 0;
    }
}
