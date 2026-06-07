@php
    $title = __('Chargeable Account Summary Report');
@endphp

<x-print-layout :title="$title">
    <style>
        .print-container { padding: 0.25rem !important; }
        .table th, .table td { 
            padding: 2px 4px !important; 
            font-size: 8.5px !important;
            line-height: 1.1 !important;
        }
        .mb-4 { margin-bottom: 0.5rem !important; }
        .badge { font-size: 8px !important; padding: 2px 4px !important; }
    </style>

    <div class="card border-0">
        <!-- Report Content -->
        <div class="card-body p-0 text-dark">
            <div class="mb-2">
                @if($accountId)
                    <span class="badge border me-2">Account: {{ $accounts->firstWhere('id', $accountId)?->name }}</span>
                @endif
                @if($dateFrom || $dateTo)
                    <span class="badge border">Period: {{ $dateFrom ? \Carbon\Carbon::parse($dateFrom)->format('M d, Y') : 'Start' }} to {{ $dateTo ? \Carbon\Carbon::parse($dateTo)->format('M d, Y') : 'End' }}</span>
                @endif
            </div>
            
            <div class="table-responsive">
                <table class="table table-bordered mb-0 border-secondary">
                    <thead class="table-light">
                        <tr class="text-uppercase small fw-bold">
                            <th>Account Name</th>
                            <th class="text-end">KM</th>
                            <th class="text-end">HR</th>
                            <th class="text-end">Budgeted</th>
                            <th class="text-end">Unbudgeted</th>
                            <th class="text-end">Total Calc</th>
                            <th class="text-end">Budget</th>
                            <th class="text-end">Rem.</th>
                        </tr>
                    </thead>
                    <tbody class="border-secondary">
                        @php
                            $grandTotalKm = 0; $grandTotalHours = 0; $grandTotalBudgeted = 0;
                            $grandTotalUnbudgeted = 0; $grandTotalTotalCalc = 0;
                            $grandTotalTotalBudget = 0; $grandTotalRemaining = 0;
                        @endphp
                        @forelse($accountSummaries as $account)
                            @php
                                $grandTotalKm += $account['total_km'];
                                $grandTotalHours += $account['total_hours'];
                                $grandTotalBudgeted += $account['budgeted_fuel'];
                                $grandTotalUnbudgeted += $account['unbudgeted_fuel'];
                                $grandTotalTotalCalc += $account['total_calculated_fuel'];
                                $grandTotalTotalBudget += $account['total_budget'];
                                $accountOffset = $account['offset_fuel'] ?? 0;
                            @endphp
                            <tr class="table-active fw-bold">
                                <td>
                                    {{ $account['name'] }}
                                    @if(isset($account['classification']) && $account['classification'] === 'Scoped')
                                        <div style="font-size: 7px; color: #666; font-weight: normal;">
                                            Scope: {{ $account['start_date'] ? \Carbon\Carbon::parse($account['start_date'])->format('M d, Y') : 'N/A' }} - {{ $account['end_date'] ? \Carbon\Carbon::parse($account['end_date'])->format('M d, Y') : 'N/A' }}
                                        </div>
                                    @else
                                        <div style="font-size: 7px; color: #666; font-weight: normal;">
                                            Period: {{ $dateFrom ? \Carbon\Carbon::parse($dateFrom)->format('M d, Y') : 'Start' }} - {{ $dateTo ? \Carbon\Carbon::parse($dateTo)->format('M d, Y') : 'End' }}
                                        </div>
                                    @endif
                                    @if($accountOffset > 0)
                                        <div style="font-size: 7px; color: #666; font-weight: normal;">Off: {{ number_format($accountOffset, 2) }}</div>
                                    @endif
                                </td>
                                <td class="text-end">—</td>
                                <td class="text-end">—</td>
                                <td class="text-end">—</td>
                                <td class="text-end">—</td>
                                <td class="text-end">—</td>
                                <td class="text-end">—</td>
                                <td class="text-end">
                                    @php 
                                        $remaining = ($account['total_budget'] - ($account['total_calculated_fuel'] + $accountOffset)); 
                                        if ($account['total_budget'] > 0) {
                                            $grandTotalRemaining += $remaining; 
                                        }
                                    @endphp
                                    —
                                </td>
                            </tr>

                            @if(isset($account['sub_accounts']) && count($account['sub_accounts']) > 0)
                                @foreach($account['sub_accounts'] as $subAccount)
                                    <tr style="font-size: 8px !important;">
                                        <td class="ps-3">└ {{ $subAccount['name'] }}</td>
                                        <td class="text-end">{{ number_format($subAccount['total_km'], 2) }}</td>
                                        <td class="text-end">{{ number_format($subAccount['total_hours'], 2) }}</td>
                                        <td class="text-end">{{ number_format($subAccount['budgeted_fuel'], 2) }}</td>
                                        <td class="text-end">{{ number_format($subAccount['unbudgeted_fuel'], 2) }}</td>
                                        <td class="text-end">{{ number_format($subAccount['total_calculated_fuel'], 2) }}</td>
                                        <td class="text-end">{{ number_format($subAccount['total_budget'], 2) }}</td>
                                        <td class="text-end">
                                            @php $subRemaining = ($subAccount['total_budget'] - $subAccount['total_calculated_fuel']); @endphp
                                            {{ $subAccount['total_budget'] > 0 ? number_format($subRemaining, 2) : '0.00' }}
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        @empty
                            <tr><td colspan="8" class="text-center">No data found.</td></tr>
                        @endforelse
                        
                        @if(count($accountSummaries) > 0)
                            <tr class="table-primary fw-bold">
                                <td class="text-end">GRAND TOTAL:</td>
                                <td class="text-end">{{ number_format($grandTotalKm, 2) }}</td>
                                <td class="text-end">{{ number_format($grandTotalHours, 2) }}</td>
                                <td class="text-end">{{ number_format($grandTotalBudgeted, 2) }}</td>
                                <td class="text-end">{{ number_format($grandTotalUnbudgeted, 2) }}</td>
                                <td class="text-end">{{ number_format($grandTotalTotalCalc, 2) }}</td>
                                <td class="text-end">{{ number_format($grandTotalTotalBudget, 2) }}</td>
                                <td class="text-end">{{ number_format($grandTotalRemaining, 2) }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-print-layout>