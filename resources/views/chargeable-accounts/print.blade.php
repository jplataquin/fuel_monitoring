<x-print-layout :title="__('Chargeable Account Report')">
    <div class="mb-4">
        <div class="row g-3">
            <div class="col-md-3">
                <h4 class="small fw-bold text-uppercase text-secondary tracking-wider mb-1">Account Name</h4>
                <p class="h5 fw-bold text-dark mb-0">{{ $chargeableAccount->name }}</p>
            </div>
            <div class="col-md-3">
                <h4 class="small fw-bold text-uppercase text-secondary tracking-wider mb-1">Classification</h4>
                <p class="h5 fw-bold text-dark mb-0">{{ $chargeableAccount->classification }}</p>
                @if($chargeableAccount->classification === 'Scoped')
                    <p class="small text-secondary mb-0">
                        {{ $chargeableAccount->start_date?->format('M d, Y') }} - {{ $chargeableAccount->end_date?->format('M d, Y') }}
                    </p>
                @endif
            </div>
            <div class="col-md-3">
                <h4 class="small fw-bold text-uppercase text-secondary tracking-wider mb-1">Status</h4>
                <span class="badge rounded-pill border border-dark text-dark px-3 py-1 fw-bold uppercase">
                    {{ $chargeableAccount->status }}
                </span>
            </div>
            <div class="col-md-3">
                <h4 class="small fw-bold text-uppercase text-secondary tracking-wider mb-1">Print Date</h4>
                <p class="h5 fw-bold text-dark mb-0 font-monospace">{{ now()->format('M d, Y') }}</p>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6 col-12">
            <div class="border border-dark rounded-3 p-3 text-center bg-light">
                <h4 class="small fw-bold text-success text-uppercase tracking-wider mb-1">Total Approved Budget</h4>
                <p class="h4 fw-black text-success mb-0 font-monospace">
                    @php
                        $totalApproved = $chargeableAccount->subAccounts->flatMap->budgets->where('status', 'Approved')->sum('budget_quantity');
                    @endphp
                    {{ number_format($totalApproved, 2) }} <span class="h6 text-secondary text-uppercase">L</span>
                </p>
            </div>
        </div>
        <div class="col-md-6 col-12">
            <div class="border border-dark rounded-3 p-3 text-center bg-light">
                <h4 class="small fw-bold text-warning text-uppercase tracking-wider mb-1">Total Pending Budget</h4>
                <p class="h4 fw-black text-warning mb-0 font-monospace">
                    @php
                        $totalPending = $chargeableAccount->subAccounts->flatMap->budgets->where('status', 'Pending')->sum('budget_quantity');
                    @endphp
                    {{ number_format($totalPending, 2) }} <span class="h6 text-secondary text-uppercase">L</span>
                </p>
            </div>
        </div>
    </div>

    <div class="mb-4">
        <h2 class="h3 fw-bold text-uppercase border-bottom border-dark pb-2 mb-3">Sub-Account Budget Breakdown</h2>
        <div class="table-responsive rounded-3 border border-dark">
            <table class="table table-bordered text-dark mb-0">
                <thead>
                    <tr class="bg-light">
                        <th class="ps-4 py-2 small fw-bold text-dark text-uppercase">Sub Account</th>
                        <th class="px-4 py-2 small fw-bold text-dark text-uppercase text-end">Approved Budget (L)</th>
                        <th class="px-4 py-2 small fw-bold text-dark text-uppercase text-end">Pending Budget (L)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($chargeableAccount->subAccounts as $subAccount)
                        <tr>
                            <td class="ps-4 py-2 small fw-bold text-dark">{{ $subAccount->name }}</td>
                            <td class="px-4 py-2 small text-success fw-bold text-end font-monospace">
                                {{ number_format($subAccount->budgets->where('status', 'Approved')->sum('budget_quantity'), 2) }} L
                            </td>
                            <td class="px-4 py-2 small text-warning fw-bold text-end font-monospace">
                                {{ number_format($subAccount->budgets->where('status', 'Pending')->sum('budget_quantity'), 2) }} L
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-4 text-center text-secondary small">
                                No sub-accounts found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-print-layout>
