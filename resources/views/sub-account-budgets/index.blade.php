<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 font-weight-bold text-white mb-0 print-text-black">
                {{ __('Budget') }}
            </h2>
            <div class="print-hidden">
                <button onclick="window.print()" class="btn btn-primary rounded-pill px-4 fw-bold small text-uppercase tracking-widest shadow">
                    <svg class="me-2" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Print
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-5 print-py-2">
        <div class="container-fluid px-4 print-px-0">
            <div class="card bg-dark border-secondary shadow-lg rounded-4 overflow-hidden print-bg-white print-shadow-none print-rounded-0 print-border-black print-border-2">
                <!-- Filter -->
                <div class="card-header bg-secondary bg-opacity-10 border-secondary border-opacity-25 p-4 print-hidden">
                    <form method="GET" action="{{ route('account-budgets.index') }}" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label text-secondary small fw-bold text-uppercase tracking-widest">Search by Chargeable Account</label>
                            <select name="chargeable_account_id" onchange="this.form.submit()" class="form-select bg-dark border-secondary text-white rounded-3 p-2.5 focus-ring focus-ring-primary">
                                <option value="">Select Chargeable Account</option>
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc->id }}" {{ request('chargeable_account_id') == $acc->id ? 'selected' : '' }}>
                                        {{ $acc->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>

                <div class="card-body p-0 text-light print-text-black">
                    <div class="table-responsive print-overflow-visible">
                        <table class="table table-dark table-hover mb-0 print-table print-border-black">
                            <thead class="print-border-bottom print-border-black">
                                <tr class="bg-secondary bg-opacity-5">
                                    <th class="px-4 py-3 text-uppercase small fw-bold text-secondary tracking-wider print-text-black">Hierarchy / Reference</th>
                                    <th class="px-4 py-3 text-uppercase small fw-bold text-secondary tracking-wider text-end print-text-black">Budget Qty</th>
                                    <th class="px-4 py-3 text-uppercase small fw-bold text-secondary tracking-wider text-center print-text-black">Status</th>
                                    <th class="px-4 py-3 text-uppercase small fw-bold text-secondary tracking-wider text-center print-text-black">Date Allocated</th>
                                    <th class="px-4 py-3 text-uppercase small fw-bold text-secondary tracking-wider print-text-black">Remarks</th>
                                    <th class="px-4 py-3 text-uppercase small fw-bold text-secondary tracking-wider text-end print-hidden" style="width: 150px;">Actions</th>
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
                                        <tr class="bg-dark border-top border-4 border-primary print-bg-light print-border-black">
                                            <td colspan="6" class="px-4 py-3">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div class="d-flex align-items-center">
                                                        <div class="rounded-3 bg-primary bg-opacity-25 p-2 me-3 print-hidden">
                                                            <svg class="text-primary" width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                                        </div>
                                                        <div>
                                                            <h3 class="small fw-bold text-primary text-uppercase tracking-widest mb-0">Primary Account</h3>
                                                            <span class="h5 fw-black text-white text-uppercase print-text-black">{{ $budget->subAccount?->chargeableAccount?->name ?? 'Unknown' }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="text-end">
                                                        <h3 class="small fw-bold text-primary text-uppercase tracking-wider mb-0 opacity-75 print-text-black">Account Total</h3>
                                                        <p class="h5 fw-black text-white font-monospace mb-0 print-text-black">
                                                            {{ number_format($budget->subAccount?->chargeableAccount?->subAccounts->flatMap(function($s) { return $s->budgets; })->where('status', 'Approved')->sum('budget_quantity') ?? 0, 2) }} L
                                                        </p>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        @php $lastChargeableId = $currentChargeableId; $lastSubId = null; @endphp
                                    @endif

                                    {{-- Level 2: Sub-Account Header --}}
                                    @if($lastSubId !== $currentSubId)
                                        <tr class="bg-secondary bg-opacity-10 border-top border-secondary border-opacity-25 print-bg-light print-border-black">
                                            <td colspan="6" class="px-5 py-2">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div class="d-flex align-items-center">
                                                        <div class="bg-primary bg-opacity-25 rounded-pill me-3 print-hidden" style="width: 4px; height: 24px;"></div>
                                                        <span class="small fw-bold text-primary text-uppercase tracking-widest print-text-black">└ {{ $budget->subAccount?->name ?? 'Unknown Sub' }}</span>
                                                    </div>
                                                    <div class="text-end">
                                                        <span class="small fw-bold text-secondary text-uppercase me-2 print-text-black">Sub Total:</span>
                                                        <span class="small fw-bold text-white font-monospace print-text-black">{{ number_format($budget->subAccount?->budgets->where('status', 'Approved')->sum('budget_quantity') ?? 0, 2) }} L</span>
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
                                    <tr class="{{ $budget->status === 'Rejected' ? 'print-hidden' : '' }}">
                                        <td class="px-5 py-3 align-middle ps-5">
                                            <div class="d-flex align-items-center ps-4">
                                                <div class="border-top border-secondary border-opacity-50 me-3 print-hidden" style="width: 15px;"></div>
                                                <a href="{{ route('account-budgets.show', $budget) }}" class="small fw-medium text-secondary text-decoration-none hover-text-white print-text-black">
                                                    <span class="print-hidden">Allocation #{{ $count }}</span>
                                                    <span class="d-none print-inline">- {{ $budget->id }}</span>
                                                </a>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 align-middle text-end text-primary fw-bold font-monospace print-text-black">
                                            {{ number_format($budget->budget_quantity, 2) }} L
                                        </td>
                                        <td class="px-4 py-3 align-middle text-center print-text-black">
                                            <span class="badge rounded-pill fw-bold text-uppercase smaller 
                                                {{ $budget->status === 'Approved' ? 'bg-success' : 
                                                   ($budget->status === 'Rejected' ? 'bg-danger' : 'bg-warning text-dark') }}">
                                                {{ $budget->status }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 align-middle text-center text-secondary small print-text-black">
                                            <span class="badge bg-secondary bg-opacity-25 text-secondary print-bg-transparent print-p-0">{{ $budget->created_at->format('M d, Y') }}</span>
                                        </td>
                                        <td class="px-4 py-3 align-middle text-secondary small fst-italic print-text-black print-fst-normal">
                                            {{ Str::limit($budget->remarks, 40) ?: '—' }}
                                        </td>
                                        <td class="px-4 py-3 align-middle text-end print-hidden">
                                            <div class="btn-group">
                                                @php $userRole = auth()->user()->role; @endphp
                                                @if(in_array($userRole, ['administrator', 'moderator']) && $budget->status === 'Pending')
                                                    <form action="{{ route('account-budgets.approve', $budget) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="btn btn-sm btn-outline-success border-opacity-25 rounded-3 me-1" onclick="return confirm('Are you sure you want to APPROVE this budget?')" title="Approve Budget">
                                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('account-budgets.reject', $budget) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger border-opacity-25 rounded-3 me-1" onclick="return confirm('Are you sure you want to REJECT this budget?')" title="Reject Budget">
                                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                                                        </button>
                                                    </form>
                                                @endif
                                                <a href="{{ route('account-budgets.show', $budget) }}" class="btn btn-link text-secondary p-1" title="View Details">
                                                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                                </a>
                                                @if(!($userRole === 'budgeteer' && $budget->status !== 'Pending'))
                                                    <a href="{{ route('account-budgets.edit', $budget) }}" class="btn btn-link text-warning p-1" title="Edit Budget">
                                                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                                    </a>
                                                    <form action="{{ route('account-budgets.destroy', $budget) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-link text-danger p-1" onclick="return confirm('Are you sure you want to delete this budget?')" title="Delete Budget">
                                                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>

                                    @php $count++; @endphp
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-5 text-center text-secondary">
                                            <div class="py-5">
                                                <svg class="mb-3 opacity-25" width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                <p class="small fw-bold text-uppercase tracking-widest mb-0">
                                                    @if(request('chargeable_account_id'))
                                                        No budgets found for this account
                                                    @else
                                                        Please select a Chargeable Account to view budget allocations
                                                    @endif
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="mt-4 print-hidden">
                {{ $budgets->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
