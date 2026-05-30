@if(count($assetVarianceData) > 0)
    <div class="row g-4">
        @foreach($assetVarianceData as $asset)
            @php
                $variance = $asset['variance_percent'];
                $statusColor = 'text-secondary';
                $varianceType = 'normal';
                if ($variance >= 10) {
                    $statusColor = 'text-danger';
                    $varianceType = 'red';
                } elseif ($variance < 0) {
                    $statusColor = 'text-info'; // Using info for teal in current theme
                    $varianceType = 'blue';
                }
            @endphp
            <div class="col-12 col-sm-6 col-md-4 col-lg-3 asset-card-item" data-variance-type="{{ $varianceType }}">
                <div class="card h-100 bg-dark border-secondary border-opacity-25 rounded-4 shadow-sm hover-bg-light hover-bg-opacity-5 transition-all">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h4 class="h6 fw-bold text-light mb-1">{{ $asset['fleet_no'] }}</h4>
                                <p class="small text-secondary mb-0">{{ $asset['plate_no'] ?? 'No Plate' }}</p>
                            </div>
                            <div class="bg-secondary bg-opacity-10 p-2 rounded-3">
                                <svg width="16" height="16" class="text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" /></svg>
                            </div>
                        </div>
                        
                        <div class="pt-2 border-top border-secondary border-opacity-10">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small text-secondary text-uppercase tracking-wider">Avg Variance</span>
                                <span class="fw-bold font-monospace {{ $statusColor }}">
                                    {{ $variance > 0 ? '+' : '' }}{{ number_format($variance, 2) }}%
                                </span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="small text-secondary opacity-50 text-uppercase tracking-wider" style="font-size: 0.6rem;">Orders</span>
                                <span class="small text-secondary fw-bold">{{ $asset['order_count'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="card bg-dark border-secondary shadow-sm rounded-4">
        <div class="card-body text-center py-5">
            <p class="text-secondary mb-0 small text-uppercase fw-bold tracking-widest">No asset data available for this period.</p>
        </div>
    </div>
@endif
