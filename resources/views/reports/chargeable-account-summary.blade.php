<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-column flex-md-row justify-content-md-between align-items-md-center gap-3">
            <h2 class="h4 font-weight-bold text-light mb-0">
                {{ __('Chargeable Account Summary Report') }}
            </h2>
            <div class="d-flex align-items-center gap-2 d-print-none">
                <button onclick="window.print()" class="btn btn-light rounded-pill px-4 shadow-sm fw-bold text-uppercase small">
                    <svg class="me-2" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Print Report
                </button>
            </div>
        </div>
    </x-slot>

    <style>
        @media print {
            .card { border: none !important; shadow: none !important; }
            .card-header, .btn, .d-print-none { display: none !important; }
            .bg-dark { background-color: white !important; color: black !important; }
            .text-light, .text-secondary, .text-primary, .text-success, .text-info, .text-danger { color: black !important; }
            .table { color: black !important; border-collapse: collapse !important; }
            .table.border-secondary { border-color: black !important; }
            .table-dark { --bs-table-bg: white !important; --bs-table-color: black !important; }
            .table-secondary, .table-primary, .table-success, .table-info, .table-warning, .table-danger { --bs-table-bg: #f8f9fa !important; --bs-table-color: black !important; }
            .table-striped tbody tr:nth-of-type(odd) { background-color: rgba(0, 0, 0, 0.05) !important; }
            .table-active { background-color: rgba(0, 0, 0, 0.1) !important; }
            .container-xl { max-width: 100% !important; padding: 0 !important; margin: 0 !important; }
            .p-4 { padding: 0.5rem !important; }
            .mb-4 { margin-bottom: 0.5rem !important; }
        }
    </style>

    <div class="py-5">
        <div class="container-xl" style="max-width: 1280px;">
            <div class="card bg-dark border-secondary shadow-lg rounded-4 overflow-hidden">
                
                <!-- Report Filter Form -->
                <div class="card-header bg-dark border-secondary p-4 d-print-none">
                    <form action="{{ route('reports.chargeable-accounts') }}" method="GET" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label for="account_id" class="form-label small fw-bold text-secondary text-uppercase tracking-wider">Chargeable Account</label>
                            <select name="account_id" id="account_id" class="form-select bg-dark text-light border-secondary">
                                <option value="">All Accounts</option>
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc->id }}" {{ $accountId == $acc->id ? 'selected' : '' }}>{{ $acc->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="date_from" class="form-label small fw-bold text-secondary text-uppercase tracking-wider">Date From</label>
                            <input type="date" name="date_from" id="date_from" value="{{ $dateFrom }}" class="form-control bg-dark text-light border-secondary" required>
                        </div>
                        <div class="col-md-3">
                            <label for="date_to" class="form-label small fw-bold text-secondary text-uppercase tracking-wider">Date To</label>
                            <input type="date" name="date_to" id="date_to" value="{{ $dateTo }}" class="form-control bg-dark text-light border-secondary" required>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold text-uppercase small shadow-sm py-2">
                                Generate
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Report Content -->
                <div class="card-body p-0 text-light">
                    <div class="d-none d-print-block p-4 text-center border-bottom border-secondary">
                        <h2 class="h3 fw-black text-uppercase tracking-widest">Chargeable Account Summary Report</h2>
                        @if($dateFrom || $dateTo)
                            <p class="small fw-bold mt-2">Date: {{ $dateFrom ? \Carbon\Carbon::parse($dateFrom)->format('M d, Y') : 'Any' }} - {{ $dateTo ? \Carbon\Carbon::parse($dateTo)->format('M d, Y') : 'Any' }}</p>
                        @endif
                    </div>
                    
                    <div class="table-responsive d-print-overflow-visible">
                        <table class="table table-dark table-hover mb-0 d-print-table d-print-text-dark border-secondary">
                            <thead class="table-secondary">
                                <tr class="text-uppercase small fw-bold tracking-widest">
                                    <th class="px-4 py-3 border-secondary d-print-p-1">Account Name</th>
                                    <th class="px-4 py-3 border-secondary text-end d-print-p-1">Total KM</th>
                                    <th class="px-4 py-3 border-secondary text-end d-print-p-1">Total HR</th>
                                    <th class="px-4 py-3 border-secondary text-end d-print-p-1">Budgeted Fuel</th>
                                    <th class="px-4 py-3 border-secondary text-end d-print-p-1">Unbudgeted Fuel</th>
                                    <th class="px-4 py-3 border-secondary text-end d-print-p-1">Total Calc. Fuel</th>
                                    <th class="px-4 py-3 border-secondary text-end d-print-p-1">Total Budget</th>
                                    <th class="px-4 py-3 border-secondary text-end d-print-p-1">Remaining</th>
                                </tr>
                            </thead>
                            <tbody class="border-secondary">
                                @php
                                    $grandTotalKm = 0;
                                    $grandTotalHours = 0;
                                    $grandTotalBudgeted = 0;
                                    $grandTotalUnbudgeted = 0;
                                    $grandTotalTotalCalc = 0;
                                    $grandTotalTotalBudget = 0;
                                    $grandTotalRemaining = 0;
                                @endphp
                                @forelse($accountSummaries as $account)
                                    @php
                                        $grandTotalKm += $account['total_km'];
                                        $grandTotalHours += $account['total_hours'];
                                        $grandTotalBudgeted += $account['budgeted_fuel'];
                                        $grandTotalUnbudgeted += $account['unbudgeted_fuel'];
                                        $grandTotalTotalCalc += $account['total_calculated_fuel'];
                                        $grandTotalTotalBudget += $account['total_budget'];
                                    @endphp
                                    <tr class="table-active">
                                        <td class="px-4 py-3 fw-bold text-primary border-secondary">
                                            {{ $account['name'] }}
                                        </td>
                                        <td class="px-4 py-3 text-end font-monospace fw-bold border-secondary">
                                            {{ number_format($account['total_km'], 2) }}
                                        </td>
                                        <td class="px-4 py-3 text-end font-monospace fw-bold border-secondary">
                                            {{ number_format($account['total_hours'], 2) }}
                                        </td>
                                        <td class="px-4 py-3 text-end font-monospace fw-bold text-success border-secondary">
                                            {{ number_format($account['budgeted_fuel'], 2) }} L
                                        </td>
                                        <td class="px-4 py-3 text-end font-monospace fw-bold text-danger border-secondary">
                                            {{ number_format($account['unbudgeted_fuel'], 2) }} L
                                        </td>
                                        <td class="px-4 py-3 text-end font-monospace fw-bold border-secondary">
                                            {{ number_format($account['total_calculated_fuel'], 2) }} L
                                        </td>
                                        <td class="px-4 py-3 text-end font-monospace fw-bold text-primary border-secondary">
                                            {{ number_format($account['total_budget'], 2) }} L
                                        </td>
                                        <td class="px-4 py-3 text-end font-monospace fw-bold text-primary border-secondary">
                                            @php 
                                                $remaining = ($account['total_budget'] - $account['total_calculated_fuel']);
                                            @endphp
                                            @if($account['total_budget'] > 0)
                                                {{ number_format($remaining, 2) }} L
                                                @php $grandTotalRemaining += $remaining; @endphp
                                            @else
                                                0.00 L
                                            @endif
                                        </td>
                                    </tr>

                                    @if(isset($account['sub_accounts']) && count($account['sub_accounts']) > 0)
                                        @foreach($account['sub_accounts'] as $subAccount)
                                            <tr>
                                                <td class="px-4 py-2 small text-secondary border-secondary" style="padding-left: 2.5rem !important;">
                                                    └ {{ $subAccount['name'] }}
                                                </td>
                                                <td class="px-4 py-2 text-end font-monospace small border-secondary">
                                                    {{ number_format($subAccount['total_km'], 2) }}
                                                </td>
                                                <td class="px-4 py-2 text-end font-monospace small border-secondary">
                                                    {{ number_format($subAccount['total_hours'], 2) }}
                                                </td>
                                                <td class="px-4 py-2 text-end font-monospace small fw-bold text-success border-secondary">
                                                    {{ number_format($subAccount['budgeted_fuel'], 2) }} L
                                                </td>
                                                <td class="px-4 py-2 text-end font-monospace small fw-bold text-danger border-secondary">
                                                    {{ number_format($subAccount['unbudgeted_fuel'], 2) }} L
                                                </td>
                                                <td class="px-4 py-2 text-end font-monospace small fw-bold border-secondary">
                                                    {{ number_format($subAccount['total_calculated_fuel'], 2) }} L
                                                </td>
                                                <td class="px-4 py-2 text-end font-monospace small text-primary border-secondary">
                                                    {{ number_format($subAccount['total_budget'], 2) }} L
                                                </td>
                                                <td class="px-4 py-2 text-end font-monospace small text-primary border-secondary">
                                                    @php 
                                                        $subRemaining = ($subAccount['total_budget'] - $subAccount['total_calculated_fuel']);
                                                    @endphp
                                                    @if($subAccount['total_budget'] > 0)
                                                        {{ number_format($subRemaining, 2) }} L
                                                    @else
                                                        0.00 L
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-4 py-5 text-center border-secondary">
                                            <div class="py-5 d-print-none">
                                                <div class="bg-secondary bg-opacity-10 d-inline-flex p-3 rounded-4 mb-3">
                                                    <svg width="32" height="32" class="text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                </div>
                                                <p class="fw-bold mb-1">No report data to display.</p>
                                                <p class="small text-secondary mb-0">Please select parameters to generate the report.</p>
                                            </div>
                                            <div class="d-none d-print-block text-dark">
                                                No records found for the selected parameters.
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                                
                                @if(count($accountSummaries) > 0)
                                    <tr class="table-primary border-top border-secondary">
                                        <td class="px-4 py-4 text-end h6 fw-bold text-uppercase tracking-widest mb-0 border-secondary">
                                            Grand Total:
                                        </td>
                                        <td class="px-4 py-4 text-end font-monospace fw-bold border-secondary">
                                            {{ number_format($grandTotalKm, 2) }}
                                        </td>
                                        <td class="px-4 py-4 text-end font-monospace fw-bold border-secondary">
                                            {{ number_format($grandTotalHours, 2) }}
                                        </td>
                                        <td class="px-4 py-4 text-end font-monospace fw-bold text-success border-secondary">
                                            {{ number_format($grandTotalBudgeted, 2) }} L
                                        </td>
                                        <td class="px-4 py-4 text-end font-monospace fw-bold text-danger border-secondary">
                                            {{ number_format($grandTotalUnbudgeted, 2) }} L
                                        </td>
                                        <td class="px-4 py-4 text-end font-monospace fw-bold border-secondary">
                                            {{ number_format($grandTotalTotalCalc, 2) }} L
                                        </td>
                                        <td class="px-4 py-4 text-end font-monospace fw-bold text-primary border-secondary">
                                             {{ number_format($grandTotalTotalBudget, 2) }} L
                                        </td>
                                        <td class="px-4 py-4 text-end font-monospace h5 fw-bold text-primary border-secondary mb-0">
                                            {{ number_format($grandTotalRemaining, 2) }} L
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
