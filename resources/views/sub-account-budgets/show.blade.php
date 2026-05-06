<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 font-weight-bold text-white mb-0">
                {{ __('Budget Details') }}
            </h2>
            <div class="d-flex align-items-center gap-2">
                @if(in_array(Auth::user()->role, ['administrator', 'moderator']) && $accountBudget->status === 'Pending')
                    <form action="{{ route('account-budgets.approve', $accountBudget) }}" method="POST" class="d-inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold small text-uppercase tracking-widest shadow hover-scale" onclick="return confirm('Are you sure you want to APPROVE this budget?')">
                            <svg class="me-2" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                            {{ __('Approve') }}
                        </button>
                    </form>
                    <form action="{{ route('account-budgets.reject', $accountBudget) }}" method="POST" class="d-inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold small text-uppercase tracking-widest shadow hover-scale" onclick="return confirm('Are you sure you want to REJECT this budget?')">
                            <svg class="me-2" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                            {{ __('Reject') }}
                        </button>
                    </form>
                @endif
                
                <a href="{{ route('account-budgets.edit', $accountBudget) }}" class="btn btn-outline-secondary rounded-circle p-2" title="Edit Budget">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                </a>

                <a href="{{ route('account-budgets.index') }}" class="btn btn-link text-secondary text-decoration-none small fw-bold text-uppercase tracking-widest ms-2">
                    Back to List
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-5">
        <div class="container" style="max-width: 900px;">
            <div class="card bg-dark border-secondary shadow-lg rounded-4 overflow-hidden">
                <div class="row g-0">
                    <div class="col-md-6 border-bottom border-md-0 border-end-md border-secondary border-opacity-25 p-5">
                        <div class="d-flex flex-column gap-5">
                            <div>
                                <h3 class="text-info small fw-bold text-uppercase tracking-widest mb-3 d-flex align-items-center">
                                    <span class="bg-info bg-opacity-25 me-3" style="width: 32px; height: 1px;"></span>
                                    Sub Account
                                </h3>
                                <p class="h3 text-white fw-bold mb-1">
                                    {{ $accountBudget->subAccount->name ?? '—' }}
                                </p>
                                <p class="small text-secondary text-uppercase tracking-widest fw-medium mb-0">
                                    Parent: {{ $accountBudget->subAccount->chargeableAccount->name ?? '—' }}
                                </p>
                            </div>

                            <div>
                                <h3 class="text-info small fw-bold text-uppercase tracking-widest mb-3 d-flex align-items-center">
                                    <span class="bg-info bg-opacity-25 me-3" style="width: 32px; height: 1px;"></span>
                                    Budget Quantity
                                </h3>
                                <p class="display-5 text-info fw-black font-monospace mb-0">
                                    {{ number_format($accountBudget->budget_quantity, 2) }} <span class="h5 text-secondary fw-bold ms-1">Liters</span>
                                </p>
                            </div>

                            <div>
                                <h3 class="text-info small fw-bold text-uppercase tracking-widest mb-3 d-flex align-items-center">
                                    <span class="bg-info bg-opacity-25 me-3" style="width: 32px; height: 1px;"></span>
                                    Status
                                </h3>
                                <span class="badge rounded-pill fw-black text-uppercase tracking-widest py-2 px-3 border
                                    {{ $accountBudget->status === 'Approved' ? 'bg-success bg-opacity-10 text-success border-success border-opacity-25' : 
                                       ($accountBudget->status === 'Rejected' ? 'bg-danger bg-opacity-10 text-danger border-danger border-opacity-25' : 
                                       'bg-warning bg-opacity-10 text-warning border-warning border-opacity-25') }}">
                                    <span class="d-inline-block bg-current rounded-circle me-2" style="width: 8px; height: 8px; background-color: currentColor;"></span>
                                    {{ $accountBudget->status }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 bg-secondary bg-opacity-10 p-5">
                        <div class="d-flex flex-column gap-5">
                            <div>
                                <h3 class="text-info small fw-bold text-uppercase tracking-widest mb-3 d-flex align-items-center">
                                    <span class="bg-info bg-opacity-25 me-3" style="width: 32px; height: 1px;"></span>
                                    Remarks
                                </h3>
                                <p class="text-white small fst-italic lh-lg mb-0">
                                    {{ $accountBudget->remarks ?: 'No remarks provided.' }}
                                </p>
                            </div>

                            <div class="pt-4 border-top border-secondary border-opacity-25 d-flex flex-column gap-3">
                                <div class="d-flex justify-content-between small">
                                    <span class="text-secondary fw-bold text-uppercase tracking-widest">Allocated By:</span>
                                    <span class="text-white fw-medium">{{ $accountBudget->creator->name ?? 'System' }}</span>
                                </div>
                                <div class="d-flex justify-content-between small">
                                    <span class="text-secondary fw-bold text-uppercase tracking-widest">Date:</span>
                                    <span class="text-white fw-medium">{{ $accountBudget->created_at->format('M d, Y h:i A') }}</span>
                                </div>
                                
                                @if($accountBudget->status === 'Approved')
                                    <div class="d-flex justify-content-between small pt-3 border-top border-secondary border-opacity-10">
                                        <span class="text-success fw-bold text-uppercase tracking-widest">Approved By:</span>
                                        <span class="text-white fw-medium">{{ $accountBudget->approver->name ?? '—' }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between small">
                                        <span class="text-success fw-bold text-uppercase tracking-widest">Approved At:</span>
                                        <span class="text-white fw-medium">{{ $accountBudget->approved_at ? $accountBudget->approved_at->format('M d, Y h:i A') : '—' }}</span>
                                    </div>
                                @elseif($accountBudget->status === 'Rejected')
                                    <div class="d-flex justify-content-between small pt-3 border-top border-secondary border-opacity-10">
                                        <span class="text-danger fw-bold text-uppercase tracking-widest">Rejected By:</span>
                                        <span class="text-white fw-medium">{{ $accountBudget->rejecter->name ?? '—' }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between small">
                                        <span class="text-danger fw-bold text-uppercase tracking-widest">Rejected At:</span>
                                        <span class="text-white fw-medium">{{ $accountBudget->rejected_at ? $accountBudget->rejected_at->format('M d, Y h:i A') : '—' }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
