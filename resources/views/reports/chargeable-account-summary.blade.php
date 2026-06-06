@php
    $title = __('Chargeable Account Summary Report');
@endphp

<x-app-layout :title="$title">
    <x-slot name="header">
        <div class="d-flex flex-column flex-md-row justify-content-md-between align-items-md-center gap-3">
            <h2 class="h4 font-weight-bold text-light mb-0">
                {{ $title }}
            </h2>
            <div class="d-flex align-items-center gap-2 d-print-none">
                <a href="{{ request()->fullUrlWithQuery(['print' => 1]) }}" target="_blank" class="btn btn-light rounded-pill px-4 shadow-sm fw-bold text-uppercase small">
                    <svg class="me-2" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Print Report
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-5">
        <div class="container-fluid px-md-5">
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
                    <div class="table-responsive">
                        <table class="table table-dark table-hover mb-0 border-secondary" style="min-width: 1800px;">
                            <thead class="table-secondary">
                                <tr class="text-uppercase small fw-bold tracking-widest text-nowrap">
                                    <th class="px-4 py-3 border-secondary">Account Name</th>
                                    <th class="px-4 py-3 border-secondary text-end">Total KM</th>
                                    <th class="px-4 py-3 border-secondary text-end">Total HR</th>
                                    <th class="px-4 py-3 border-secondary text-end">Budgeted Fuel</th>
                                    <th class="px-4 py-3 border-secondary text-end">Unbudgeted Fuel</th>
                                    <th class="px-4 py-3 border-secondary text-end">Total Calc. Fuel</th>
                                    <th class="px-4 py-3 border-secondary text-end">Total Budget</th>
                                    <th class="px-4 py-3 border-secondary text-end">Remaining</th>
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
                                        $accountOffset = $account['offset_fuel'] ?? 0;
                                    @endphp
                                    <tr class="table-active">
                                        <td class="px-4 py-3 fw-bold text-white border-secondary">
                                            {{ $account['name'] }}
                                            @if($accountOffset > 0)
                                                <div class="small text-warning fw-normal">Offset: {{ number_format($accountOffset, 2) }} L</div>
                                            @endif
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
                                        <td class="px-4 py-3 text-end font-monospace fw-bold text-warning border-secondary">
                                            {{ number_format($account['unbudgeted_fuel'], 2) }} L
                                        </td>
                                        <td class="px-4 py-3 text-end font-monospace fw-bold border-secondary">
                                            {{ number_format($account['total_calculated_fuel'], 2) }} L
                                        </td>
                                        <td class="px-4 py-3 text-end font-monospace fw-bold text-white border-secondary">
                                            {{ number_format($account['total_budget'], 2) }} L
                                        </td>
                                        <td class="px-4 py-3 text-end font-monospace fw-bold text-white border-secondary">
                                            @php 
                                                $remaining = ($account['total_budget'] - ($account['total_calculated_fuel'] + $accountOffset));
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
                                                <td class="px-4 py-2 text-end font-monospace small fw-bold text-warning border-secondary">
                                                    {{ number_format($subAccount['unbudgeted_fuel'], 2) }} L
                                                </td>
                                                <td class="px-4 py-2 text-end font-monospace small fw-bold border-secondary">
                                                    {{ number_format($subAccount['total_calculated_fuel'], 2) }} L
                                                </td>
                                                <td class="px-4 py-2 text-end font-monospace small text-white border-secondary">
                                                    {{ number_format($subAccount['total_budget'], 2) }} L
                                                </td>
                                                <td class="px-4 py-2 text-end font-monospace small text-white border-secondary">
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
                                            <div class="py-5">
                                                <div class="bg-secondary bg-opacity-10 d-inline-flex p-3 rounded-4 mb-3">
                                                    <svg width="32" height="32" class="text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                </div>
                                                <p class="fw-bold mb-1">No report data to display.</p>
                                                <p class="small text-secondary mb-0">Please select parameters to generate the report.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                                
                                @if(count($accountSummaries) > 0)
                                    <tr class="border-top border-secondary">
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
                                        <td class="px-4 py-4 text-end font-monospace fw-bold text-warning border-secondary">
                                            {{ number_format($grandTotalUnbudgeted, 2) }} L
                                        </td>
                                        <td class="px-4 py-4 text-end font-monospace fw-bold border-secondary">
                                            {{ number_format($grandTotalTotalCalc, 2) }} L
                                        </td>
                                        <td class="px-4 py-4 text-end font-monospace fw-bold text-white border-secondary">
                                             {{ number_format($grandTotalTotalBudget, 2) }} L
                                        </td>
                                        <td class="px-4 py-4 text-end font-monospace fw-bold text-white border-secondary mb-0">
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