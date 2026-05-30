<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h2 fw-bold text-light mb-0">
                {{ __('Dashboard') }}
            </h2>
            @if(Auth::user()->role === 'administrator')
                <a href="{{ route('public-dashboard-links.index') }}" class="btn btn-outline-primary rounded-pill px-4 fw-bold text-uppercase small shadow-sm">
                    <svg class="me-2" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" /></svg>
                    Share Dashboard
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-5">
        <div class="container-xl">
            <div class="vstack gap-5">
                <!-- Welcome Section -->
                <div class="position-relative overflow-hidden rounded-4 p-4 p-md-5 shadow-lg" style="background-color: #D0BCFF;">
                    <div class="position-relative z-1">
                        <h3 class="display-5 fw-bold text-dark mb-2 tracking-tight">
                            Good day, {{ Auth::user()->name }}
                        </h3>
                        <p class="text-dark text-opacity-75 fs-5 fw-medium mb-4 mb-md-5" style="max-width: 600px;">
                            Monitor and manage your fleet utilization with precision and ease.
                        </p>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge rounded-pill px-3 py-2 fw-bold text-uppercase tracking-widest shadow-sm" style="font-size: 0.65rem; color: #D0BCFF; background-color: #381E72;">
                                {{ Auth::user()->role }}
                            </span>
                        </div>
                    </div>
                    <div class="position-absolute top-0 end-0 mt-n5 me-n5 bg-white bg-opacity-25 rounded-circle" style="width: 320px; height: 320px; filter: blur(80px);"></div>
                    <div class="position-absolute bottom-0 end-0 mb-4 me-5 d-none d-lg-block opacity-25">
                        <x-application-logo class="fill-current text-dark" style="width: 200px; height: 200px;" />
                    </div>
                </div>

                <!-- Budget Dashboard Section -->
                <div class="vstack gap-4">
                    <div class="card bg-dark border-secondary shadow-lg rounded-4 overflow-hidden">
                        <div class="card-body p-4">
                            <form action="{{ route('dashboard') }}" method="GET" class="row g-3 align-items-end">
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
                                        Filter Dashboard
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div id="budget-grid-container">
                        @include('partials.dashboard-grid')
                    </div>
                </div>

                <!-- Asset Variance Section -->
                <div class="vstack gap-4 mt-2">
                    <div class="d-flex flex-column flex-md-row align-items-md-center gap-3">
                        <h3 class="h5 fw-bold text-light mb-0 text-uppercase tracking-widest">Asset Performance</h3>
                        <div class="flex-grow-1 border-top border-secondary border-opacity-25 d-none d-md-block"></div>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" onclick="toggleAssetFilter('red', this)" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold text-uppercase tracking-widest asset-filter-btn" style="font-size: 0.6rem;">
                                Critical (≥10%)
                            </button>
                            <button type="button" onclick="toggleAssetFilter('blue', this)" class="btn btn-sm btn-outline-info rounded-pill px-3 fw-bold text-uppercase tracking-widest asset-filter-btn" style="font-size: 0.6rem;">
                                Under (<0%)
                            </button>
                            <button type="button" onclick="toggleAssetFilter('all', this)" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold text-uppercase tracking-widest asset-filter-btn active" style="font-size: 0.6rem;">
                                Show All
                            </button>
                        </div>
                    </div>
                    
                    <div id="asset-grid-container">
                        @include('partials.asset-grid')
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        let currentAssetFilter = 'all';

        function applyAssetFilter() {
            const cards = document.querySelectorAll('.asset-card-item');
            cards.forEach(card => {
                if (currentAssetFilter === 'all' || card.dataset.varianceType === currentAssetFilter) {
                    card.classList.remove('d-none');
                } else {
                    card.classList.add('d-none');
                }
            });
        }

        function toggleAssetFilter(type, btn) {
            currentAssetFilter = type;
            
            document.querySelectorAll('.asset-filter-btn').forEach(b => {
                b.classList.remove('active', 'btn-primary', 'btn-danger', 'btn-info');
                b.classList.add('btn-outline-secondary');
                if (b.innerText.toLowerCase().includes('critical')) b.classList.replace('btn-outline-secondary', 'btn-outline-danger');
                if (b.innerText.toLowerCase().includes('under')) b.classList.replace('btn-outline-secondary', 'btn-outline-info');
                if (b.innerText.toLowerCase().includes('show all')) b.classList.replace('btn-outline-secondary', 'btn-outline-primary');
            });

            btn.classList.remove('btn-outline-danger', 'btn-outline-info', 'btn-outline-primary', 'btn-outline-secondary');
            if (type === 'red') btn.classList.add('btn-danger', 'active');
            else if (type === 'blue') btn.classList.add('btn-info', 'active');
            else btn.classList.add('btn-primary', 'active');

            applyAssetFilter();
        }

        function renderDashboardCharts(chartData) {
            chartData.forEach((data, index) => {
                const canvas = document.getElementById('chart-' + index);
                if (!canvas) return;
                const ctx = canvas.getContext('2d');
                
                const totalBudget = data.total_budget;
                const budgeted = data.budgeted_fuel;
                const unbudgeted = data.unbudgeted_fuel;
                
                let displayBudgeted = Math.min(totalBudget, budgeted);
                let remaining = Math.max(0, totalBudget - budgeted);
                let overage = Math.max(0, budgeted - totalBudget);

                let datasetsData = [];
                let backgroundColor = [];
                let labels = [];

                const utilPercent = totalBudget > 0 ? (budgeted / totalBudget) * 100 : (budgeted > 0 ? 100 : 0);
                
                let budgetedColor = '#34d399';
                if (utilPercent >= 90) {
                    budgetedColor = '#ef4444';
                } else if (utilPercent >= 75) {
                    budgetedColor = '#f59e0b';
                }

                if (totalBudget === 0 && (budgeted > 0 || unbudgeted > 0)) {
                    if (budgeted > 0) {
                        datasetsData.push(budgeted);
                        backgroundColor.push(budgetedColor);
                        labels.push('Budgeted Consumed (No Limit)');
                    }
                    if (unbudgeted > 0) {
                        datasetsData.push(unbudgeted);
                        backgroundColor.push('#8b5cf6');
                        labels.push('Unbudgeted Consumed');
                    }
                } else {
                    datasetsData.push(displayBudgeted);
                    backgroundColor.push(budgetedColor);
                    labels.push('Budgeted Consumed');

                    if (remaining > 0) {
                        datasetsData.push(remaining);
                        backgroundColor.push('rgba(73, 69, 79, 0.3)');
                        labels.push('Remaining Budget');
                    }

                    if (overage > 0) {
                        datasetsData.push(overage);
                        backgroundColor.push('#7f1d1d');
                        labels.push('Overage');
                    }

                    if (unbudgeted > 0) {
                        datasetsData.push(unbudgeted);
                        backgroundColor.push('#8b5cf6');
                        labels.push('Unbudgeted Consumed');
                    }
                }

                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: datasetsData,
                            backgroundColor: backgroundColor,
                            borderWidth: 0,
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '75%',
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let label = context.label || '';
                                        if (label) label += ': ';
                                        if (context.parsed !== null) {
                                            label += context.parsed.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' L';
                                        }
                                        return label;
                                    }
                                }
                            }
                        }
                    }
                });
            });
        }

        async function updateDashboard() {
            try {
                const url = new URL(window.location.href);
                const response = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    document.getElementById('budget-grid-container').innerHTML = data.budget_html;
                    document.getElementById('asset-grid-container').innerHTML = data.asset_html;
                    renderDashboardCharts(data.chart_data);
                    applyAssetFilter();
                    console.log('Dashboard auto-updated at ' + new Date().toLocaleTimeString());
                }
            } catch (error) {
                console.error('Dashboard update failed:', error);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            renderDashboardCharts(@json($chartData));
            setInterval(updateDashboard, 300000);
        });
    </script>

    <style>
        .hover-bg-light:hover {
            background-color: rgba(255, 255, 255, 0.05) !important;
        }
        .transition-all {
            transition: all 0.2s ease-in-out;
        }
    </style>
</x-app-layout>
