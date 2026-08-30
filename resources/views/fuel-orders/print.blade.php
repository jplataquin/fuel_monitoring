<x-print-layout :title="__('Fuel Orders Report')">
    <style>
        /* Print Styles */
        .print-container { padding: 0.25rem !important; }
        .table th, .table td { 
            padding: 4px 6px !important; 
            font-size: 9px !important;
            line-height: 1.2 !important;
            white-space: normal !important;
            word-wrap: break-word !important;
        }
        .card-body { padding: 0 !important; }
        .mb-4 { margin-bottom: 0.75rem !important; }
        .mt-4 { margin-top: 0.75rem !important; }
        .p-4 { padding: 0.75rem !important; }
        .py-4 { padding-top: 0.35rem !important; padding-bottom: 0.35rem !important; }
        .h2, .h3, .h5, .h6 { margin-bottom: 0.35rem !important; font-size: 11px !important; }
        .badge { font-size: 8.5px !important; padding: 2px 4px !important; }
    </style>

    <div class="card border-0">
        <div class="card-body text-dark">
            <!-- Header section showing filters -->
            <div class="mb-3 d-flex justify-content-between align-items-center border-bottom pb-2">
                <div>
                    <h2 class="h5 m-0" style="font-weight: bold; text-transform: uppercase;">Fuel Orders List</h2>
                    <p class="text-secondary small mb-0" style="font-size: 8px;">Print Date: {{ now()->format('M d, Y H:i') }}</p>
                </div>
                <div class="text-end">
                    @if($chargeableAccount)
                        <span class="badge border border-dark text-dark me-1">Account: {{ $chargeableAccount->name }}</span>
                    @endif
                    @if($request->filled('status'))
                        <span class="badge border border-dark text-dark me-1">Status: {{ $request->status }}</span>
                    @endif
                    @if($request->filled('fleet_no'))
                        <span class="badge border border-dark text-dark me-1">Search: {{ $request->fleet_no }}</span>
                    @endif
                </div>
            </div>

            <!-- Grand Totals Section -->
            <div class="p-3 bg-light border rounded mb-3">
                <div class="row align-items-center">
                    <div class="col-6">
                        <span class="text-secondary text-uppercase fw-bold tracking-widest" style="font-size: 8px;">Total Orders Filtered:</span>
                        <strong class="text-dark d-block" style="font-size: 11px;">{{ number_format($fuelOrders->count()) }} Orders</strong>
                    </div>
                    <div class="col-6 text-end">
                        <div class="d-inline-block text-start me-4">
                            <span class="text-secondary text-uppercase fw-bold tracking-widest d-block" style="font-size: 8px;">Total Requested Vol:</span>
                            <strong class="text-dark font-monospace" style="font-size: 11px;">{{ number_format($fuelOrders->sum('say_quantity'), 2) }} L</strong>
                        </div>
                        <div class="d-inline-block text-start">
                            <span class="text-secondary text-uppercase fw-bold tracking-widest d-block" style="font-size: 8px;">Total Actual Vol:</span>
                            <strong class="text-dark font-monospace" style="font-size: 11px;">{{ number_format($fuelOrders->sum('actual_quantity'), 2) }} L</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table of fuel orders -->
            <div class="table-responsive">
                <table class="table table-bordered mb-1">
                    <thead class="table-light">
                        <tr class="text-uppercase fw-bold text-nowrap">
                            <th style="width: 10%;">Order ID</th>
                            <th style="width: 12%;">Date Created</th>
                            <th style="width: 15%;">Asset / Fleet</th>
                            <th style="width: 25%;">Charged To / Sub-Account</th>
                            <th class="text-end" style="width: 11%;">Req Qty</th>
                            <th class="text-end" style="width: 11%;">Act Qty</th>
                            <th style="width: 10%;">Status</th>
                            <th style="width: 6%;">Created By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($fuelOrders as $order)
                            <tr>
                                <td class="align-middle font-monospace fw-bold">
                                    #{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}
                                </td>
                                <td class="align-middle">
                                    {{ $order->created_at->format('M d, Y') }}
                                </td>
                                <td class="align-middle">
                                    @if($order->asset)
                                        <strong>{{ $order->asset->fleet_no }}</strong>
                                        <span class="d-block text-secondary" style="font-size: 7px;">{{ $order->asset->assetType->name ?? 'Unknown Type' }}</span>
                                    @else
                                        <span class="text-secondary">—</span>
                                    @endif
                                </td>
                                <td class="align-middle">
                                    @if($order->asset_id)
                                        <!-- For asset based orders, list unique charged accounts from its utilization entries -->
                                        @php
                                            $accounts = $order->utilizationEntries->map(function ($entry) {
                                                return $entry->chargeableAccount->name ?? null;
                                            })->filter()->unique();
                                        @endphp
                                        <div class="small fw-semibold text-dark">
                                            {{ $accounts->implode(', ') ?: '—' }}
                                        </div>
                                    @else
                                        <!-- For direct orders -->
                                        <div class="small fw-semibold text-dark">{{ $order->chargeableAccount->name ?? '—' }}</div>
                                        @if($order->subAccount)
                                            <span class="d-block text-secondary" style="font-size: 7px;">Sub: {{ $order->subAccount->name }}</span>
                                        @endif
                                    @endif
                                </td>
                                <td class="align-middle text-end font-monospace">{{ number_format($order->say_quantity, 2) }} L</td>
                                <td class="align-middle text-end font-monospace">
                                    @if($order->actual_quantity > 0)
                                        {{ number_format($order->actual_quantity, 2) }} L
                                    @else
                                        <span class="text-secondary">—</span>
                                    @endif
                                </td>
                                <td class="align-middle">
                                    @if($order->status === 'DONE')
                                        Done
                                    @elseif($order->status === 'VOID')
                                        Void
                                    @elseif($order->is_waiver_pending)
                                        Pending Waiver
                                    @else
                                        Pending
                                    @endif
                                </td>
                                <td class="align-middle small">
                                    {{ $order->creator->name ?? '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-secondary small">
                                    No fuel orders found matching the filter criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-print-layout>