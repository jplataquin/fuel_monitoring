<x-print-layout :title="__('Utilization Entries Report')">
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
                    <h2 class="h5 fw-bold text-uppercase tracking-wider m-0">Utilization Entries List</h2>
                    <p class="text-secondary small mb-0" style="font-size: 8px;">Print Date: {{ now()->format('M d, Y H:i') }}</p>
                </div>
                <div class="text-end">
                    @if($chargeableAccount)
                        <span class="badge border border-dark text-dark me-1">Account: {{ $chargeableAccount->name }}</span>
                    @endif
                    @if($subAccount)
                        <span class="badge border border-dark text-dark me-1">Sub-Account: {{ $subAccount->display_name }}</span>
                    @endif
                    @if($asset)
                        <span class="badge border border-dark text-dark me-1">Asset: {{ $asset->fleet_no }}</span>
                    @endif
                </div>
            </div>

            <!-- Grand Total Section -->
            <div class="p-3 bg-light border rounded mb-3">
                <div class="row align-items-center">
                    <div class="col-6">
                        <span class="text-secondary text-uppercase fw-bold tracking-widest" style="font-size: 8px;">Total Consumed Fuel:</span>
                    </div>
                    <div class="col-6 text-end">
                        <span class="h4 font-monospace fw-bold text-dark mb-0">{{ number_format($totalCalculatedFuel, 2) }} L</span>
                    </div>
                </div>
            </div>

            <!-- Table of entries -->
            <div class="table-responsive">
                <table class="table table-bordered mb-1">
                    <thead class="table-light">
                        <tr class="text-uppercase fw-bold text-nowrap">
                            <th style="width: 15%;">Date</th>
                            <th style="width: 15%;">Asset</th>
                            <th style="width: 25%;">Driver/Operator</th>
                            <th style="width: 15%;">Calc Type</th>
                            <th class="text-end" style="width: 15%;">Readings / Hours</th>
                            <th class="text-end" style="width: 15%;">Comp Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($utilizationEntries as $entry)
                            <tr class="@if($entry->trashed()) table-danger opacity-75 @endif">
                                <td class="align-middle">
                                    {{ $entry->date->format('M d, Y') }}
                                    @if($entry->trashed())
                                        <span class="badge bg-danger text-white rounded-pill ms-1" style="font-size: 7px; padding: 1px 4px;">DELETED</span>
                                    @endif
                                    @if($entry->start_time && $entry->end_time)
                                        <span class="d-block text-secondary" style="font-size: 7px;">{{ $entry->start_time->format('H:i') }} - {{ $entry->end_time->format('H:i') }}</span>
                                    @endif
                                </td>
                                <td class="align-middle">
                                    <strong>{{ $entry->asset->fleet_no ?? '—' }}</strong>
                                    @if($entry->asset && $entry->asset->plate_no)
                                        <span class="d-block text-secondary" style="font-size: 7px;">{{ $entry->asset->plate_no }}</span>
                                    @endif
                                </td>
                                <td class="align-middle small">{{ $entry->driver_operator_name }}</td>
                                <td class="align-middle small">{{ $entry->calculation_type }}</td>
                                <td class="align-middle text-end font-monospace small">
                                    @if($entry->calculation_type === 'Kilometer Reading')
                                        {{ number_format($entry->start_kilometer_reading, 1) }} - {{ number_format($entry->end_kilometer_reading, 1) }} km
                                    @elseif($entry->calculation_type === 'Hour Reading')
                                        {{ number_format($entry->start_hour_reading, 1) }} - {{ number_format($entry->end_hour_reading, 1) }} hrs
                                    @elseif($entry->calculation_type === 'Actual Hours')
                                        {{ number_format($entry->actual_hours, 1) }} hrs (Act)
                                    @else
                                        Timeframe
                                    @endif
                                </td>
                                <td class="align-middle text-end font-monospace fw-bold">{{ number_format($entry->calculated_quantity, 2) }} L</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-secondary small">No utilization entries found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-print-layout>