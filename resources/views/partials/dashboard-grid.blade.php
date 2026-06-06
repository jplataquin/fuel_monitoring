@if(count($chartData) > 0)
    <div class="row g-4">
        @foreach($chartData as $index => $data)
            @php
                $totalBudget = $data['total_budget'];
                $offsetFuel = $data['offset_fuel'] ?? 0;
                $budgeted = $data['budgeted_fuel'];
                $unbudgeted = $data['unbudgeted_fuel'];
                $consumed = $data['total_calculated_fuel'];
                
                $totalConsumed = $budgeted + $offsetFuel;
                $remaining = max(0, $totalBudget - $totalConsumed);
                $overage = max(0, $totalConsumed - $totalBudget);
                $utilizationPercent = $totalBudget > 0 ? min(100, ($totalConsumed / $totalBudget) * 100) : ($totalConsumed > 0 ? 100 : 0);
                
                // Colors based on utilization
                $statusColor = '#34d399'; // Green
                if ($utilizationPercent >= 90) {
                    $statusColor = '#ef4444'; // Red
                } elseif ($utilizationPercent >= 75) {
                    $statusColor = '#f59e0b'; // Orange
                }
            @endphp
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card h-100 bg-dark border-secondary border-opacity-50 rounded-4 p-4 shadow-sm">
                    <h3 class="h5 fw-bold text-light mb-3 text-center text-truncate" title="{{ $data['name'] }}">
                        {{ $data['name'] }}
                    </h3>
                    
                    <div class="position-relative d-flex justify-content-center align-items-center mb-4" style="height: 200px;">
                        <canvas id="chart-{{ $index }}"></canvas>
                        <div class="position-absolute d-flex flex-column justify-content-center align-items-center" style="pointer-events: none;">
                            <span class="fs-4 fw-bold" style="color: {{ $statusColor }};">
                                {{ number_format($utilizationPercent, 0) }}%
                            </span>
                            <span class="small text-secondary text-uppercase tracking-widest" style="font-size: 0.65rem;">Used</span>
                        </div>
                    </div>

                    <div class="vstack gap-2">
                        <div class="d-flex justify-content-between align-items-center pb-2 border-bottom border-secondary border-opacity-25">
                            <span class="text-secondary small fw-medium text-uppercase tracking-wider">Total Budget</span>
                            <span class="text-light font-monospace fw-bold">{{ number_format($totalBudget, 2) }} L</span>
                        </div>
                        @if($offsetFuel > 0)
                        <div class="d-flex justify-content-between align-items-center pb-2 border-bottom border-secondary border-opacity-25">
                            <span class="text-secondary small fw-medium text-uppercase tracking-wider">Pre-System Offset</span>
                            <span class="font-monospace fw-bold text-warning">{{ number_format($offsetFuel, 2) }} L</span>
                        </div>
                        @endif
                        <div class="d-flex justify-content-between align-items-center pb-2 border-bottom border-secondary border-opacity-25">
                            <span class="text-secondary small fw-medium text-uppercase tracking-wider">Budgeted Consumed</span>
                            <span class="font-monospace fw-bold" style="color: {{ $statusColor }};">{{ number_format($budgeted, 2) }} L</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center pb-2 border-bottom border-secondary border-opacity-25">
                            <span class="text-secondary small fw-medium text-uppercase tracking-wider">Unbudgeted Consumed</span>
                            <span class="font-monospace fw-bold" style="color: #8b5cf6;">{{ number_format($unbudgeted, 2) }} L</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center pb-2">
                            <span class="text-secondary small fw-medium text-uppercase tracking-wider">Remaining</span>
                            <span class="font-monospace fw-bold" style="color: rgba(150, 150, 150, 0.8);">{{ number_format($remaining, 2) }} L</span>
                        </div>
                        @if($overage > 0)
                            <div class="d-flex justify-content-between align-items-center pt-2 border-top border-danger border-opacity-25">
                                <span class="text-danger small fw-bold text-uppercase tracking-wider">Overage</span>
                                <span class="font-monospace fw-bold" style="color: #7f1d1d;">{{ number_format($overage, 2) }} L</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="card bg-dark border-secondary shadow-sm rounded-4">
        <div class="card-body text-center py-5">
            <div class="bg-secondary bg-opacity-10 d-inline-flex p-3 rounded-4 mb-3">
                <svg width="32" height="32" class="text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            </div>
            <h3 class="h5 fw-bold text-light mb-1">No Data Available</h3>
            <p class="text-secondary mb-0">There are no budgets or consumption records for the selected parameters.</p>
        </div>
    </div>
@endif
