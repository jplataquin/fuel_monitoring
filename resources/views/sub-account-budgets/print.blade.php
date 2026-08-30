<x-print-layout :title="__('Sub-Account Budget Allocations Report')">
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
                    <h2 class="h5 m-0" style="font-weight: bold; text-transform: uppercase;">Budget Allocations List</h2>
                    <p class="text-secondary small mb-0" style="font-size: 8px;">Print Date: {{ now()->format('M d, Y H:i') }}</p>
                </div>
                <div class="text-end">
                    @if($account)
                        <span class="badge border border-dark text-dark me-1">Account: {{ $account->name }}</span>
                    @endif
                </div>
            </div>

            <!-- Grand Totals Section -->
            @if($budgets->isNotEmpty())
                <div class="p-3 bg-light border rounded mb-3">
                    <div class="row align-items-center">
                        <div class="col-6">
                            <span class="text-secondary text-uppercase fw-bold tracking-widest" style="font-size: 8px;">Total Allocations Filtered:</span>
                            <strong class="text-dark d-block" style="font-size: 11px;">{{ number_format($budgets->count()) }} Allocations</strong>
                        </div>
                        <div class="col-6 text-end">
                            <div class="d-inline-block text-start me-4">
                                <span class="text-secondary text-uppercase fw-bold tracking-widest d-block" style="font-size: 8px;">Approved Budget:</span>
                                <strong class="text-dark font-monospace" style="font-size: 11px;">{{ number_format($budgets->where('status', 'Approved')->sum('budget_quantity'), 2) }} L</strong>
                            </div>
                            <div class="d-inline-block text-start">
                                <span class="text-secondary text-uppercase fw-bold tracking-widest d-block" style="font-size: 8px;">Pending Approval:</span>
                                <strong class="text-dark font-monospace" style="font-size: 11px;">{{ number_format($budgets->where('status', 'Pending')->sum('budget_quantity'), 2) }} L</strong>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Table of allocations -->
            <div class="table-responsive">
                <table class="table table-bordered mb-1">
                    <thead class="table-light">
                        <tr class="text-uppercase fw-bold text-nowrap">
                            <th style="width: 40%;">Hierarchy / Reference</th>
                            <th class="text-end" style="width: 15%;">Budget Qty</th>
                            <th style="width: 12%; text-align: center;">Status</th>
                            <th style="width: 15%; text-align: center;">Date Allocated</th>
                            <th style="width: 18%;">Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php 
                            $lastChargeableId = null; 
                            $lastSubId = null;
                        @endphp

                        @forelse($budgets as $budget)
                            @php 
                                $currentChargeableId = $budget->chargeable_account_id;
                                $currentSubId = $budget->sub_account_id;
                            @endphp

                            {{-- Level 1: Chargeable Account Header --}}
                            @if($lastChargeableId !== $currentChargeableId)
                                <tr class="table-secondary" style="font-weight: bold;">
                                    <td colspan="5" class="py-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <span style="font-size: 8px; text-transform: uppercase; color: #6c757d; display: block;">Primary Account</span>
                                                <span style="font-size: 11px; text-transform: uppercase;">{{ $budget->subAccount?->chargeableAccount?->name ?? 'Unknown' }}</span>
                                            </div>
                                            <div class="text-end">
                                                <span style="font-size: 8px; text-transform: uppercase; color: #6c757d; display: block;">Account Total Approved</span>
                                                <span class="font-monospace" style="font-size: 11px;">
                                                    {{ number_format($budget->subAccount?->chargeableAccount?->subAccounts->flatMap(function($s) { return $s->budgets; })->where('status', 'Approved')->sum('budget_quantity') ?? 0, 2) }} L
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @php $lastChargeableId = $currentChargeableId; $lastSubId = null; @endphp
                            @endif

                            {{-- Level 2: Sub-Account Header --}}
                            @if($lastSubId !== $currentSubId)
                                <tr class="bg-light" style="font-weight: bold; border-top: 1px solid #dee2e6;">
                                    <td colspan="5" class="py-2" style="padding-left: 20px !important;">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <span style="font-size: 10px; text-transform: uppercase; color: #333;">└ {{ $budget->subAccount?->display_name ?? 'Unknown Sub' }}</span>
                                            </div>
                                            <div class="text-end">
                                                <span class="small text-secondary text-uppercase me-2">Sub Total Approved:</span>
                                                <span class="small font-monospace text-dark">{{ number_format($budget->subAccount?->budgets->where('status', 'Approved')->sum('budget_quantity') ?? 0, 2) }} L</span>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @php 
                                    $count = 1;
                                    $lastSubId = $currentSubId; 
                                @endphp
                            @endif

                            {{-- Level 3: Individual Budget Records --}}
                            @if($budget->status !== 'Rejected')
                                <tr>
                                    <td class="align-middle" style="padding-left: 35px !important;">
                                        Allocation #{{ $count }} <span class="text-secondary small font-monospace">(ID: {{ $budget->id }})</span>
                                    </td>
                                    <td class="align-middle text-end font-monospace" style="font-weight: bold;">
                                        {{ number_format($budget->budget_quantity, 2) }} L
                                    </td>
                                    <td class="align-middle text-center" style="font-size: 8px; text-transform: uppercase;">
                                        {{ $budget->status }}
                                    </td>
                                    <td class="align-middle text-center small text-secondary">
                                        {{ $budget->created_at->format('M d, Y') }}
                                    </td>
                                    <td class="align-middle small text-secondary">
                                        {{ $budget->remarks ?: '—' }}
                                    </td>
                                </tr>
                                @php $count++; @endphp
                            @endif
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-secondary small">
                                    @if(request()->filled('chargeable_account_id'))
                                        No budget allocations found for the selected account.
                                    @else
                                        Please select an account filter to view and print budget allocations.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-print-layout>