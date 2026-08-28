<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('chargeable-accounts.show', $subAccount->chargeableAccount) }}" class="text-info text-decoration-none fw-bold">{{ $subAccount->chargeableAccount->name }}</a></li>
                        <li class="breadcrumb-item active text-secondary" aria-current="page">{{ $subAccount->name }}</li>
                    </ol>
                </nav>
            </div>
        </div>
    </x-slot>

    <div class="py-5" x-data="{ activeTab: 'accomplishments' }">
        <div class="container" style="max-width: 1000px;">
            
            <!-- Sub-Account Info (Flat-Canvas Terminal Theme) -->
            <div class="card bg-dark border border-secondary border-opacity-25 rounded-2 p-4 mb-5">
                <div class="row align-items-center g-4">
                    <div class="col-md-3">
                        <h3 class="text-secondary small fw-bold text-uppercase tracking-widest mb-2" style="font-size: 0.75rem;">Sub-Account Name</h3>
                        <span class="h4 text-white fw-bold d-block">{{ $subAccount->display_name }}</span>
                    </div>

                    <div class="col-md-2">
                        <h3 class="text-secondary small fw-bold text-uppercase tracking-widest mb-2" style="font-size: 0.75rem;">Quantity</h3>
                        <span class="h4 text-white fw-bold font-monospace d-block">
                            @if($subAccount->quantity)
                                {{ number_format($subAccount->quantity, 2) }} <span class="h6 text-secondary text-uppercase" style="font-size: 0.75rem;">{{ $subAccount->unit }}</span>
                            @else
                                —
                            @endif
                        </span>
                    </div>
               
                    <div class="col-md-2">
                        <h3 class="text-secondary small fw-bold text-uppercase tracking-widest mb-2" style="font-size: 0.75rem;">Progress</h3>
                        <span class="h4 text-white fw-bold font-monospace d-block">{{ number_format($subAccount->accomplishment, 2) }}%</span>
                        @if($subAccount->quantity)
                            <div class="progress bg-secondary bg-opacity-10 rounded-1 mt-2 mb-1" style="height: 6px; width: 100%;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: {{ min(100.0, $subAccount->accomplishment) }}%;" aria-valuenow="{{ $subAccount->accomplishment }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <span class="text-secondary smaller fw-bold text-uppercase mt-1 d-block" style="font-size: 0.75rem;">Done: {{ number_format($subAccount->accomplishments()->sum('quantity'), 2) }} {{ $subAccount->unit }}</span>
                        @endif
                    </div>

                    <div class="col-md-2">
                        <h3 class="text-secondary small fw-bold text-uppercase tracking-widest mb-2" style="font-size: 0.75rem;">Latest Accomplishment</h3>
                        @php
                            $latestAcc = $subAccount->accomplishments()->orderBy('date_at', 'desc')->orderBy('created_at', 'desc')->first();
                        @endphp
                        <span class="h4 text-white fw-bold font-monospace d-block">
                            @if($latestAcc)
                                {{ number_format($latestAcc->quantity, 2) }} <span class="h6 text-secondary text-uppercase" style="font-size: 0.75rem;">{{ $subAccount->unit }}</span>
                            @else
                                —
                            @endif
                        </span>
                        @if($latestAcc)
                            <span class="text-secondary smaller fw-bold text-uppercase mt-1 d-block" style="font-size: 0.75rem;">On {{ $latestAcc->date_at->format('M d, Y') }}</span>
                        @endif
                    </div>

                    <div class="col-md-3 text-md-end">
                        <h3 class="text-secondary small fw-bold text-uppercase tracking-widest mb-2" style="font-size: 0.75rem;">Approved Budget</h3>
                        <p class="display-6 fw-bold text-white mb-0 font-monospace">
                            {{ number_format($subAccount->budgets()->where('status', 'Approved')->sum('budget_quantity'), 2) }} <span class="h6 text-secondary text-uppercase" style="font-size: 0.75rem;">L</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Terminal Tabbed Navigation (with High-Contrast Opacity Active States) -->
            <div class="d-flex border-bottom border-secondary border-opacity-25 mb-4 gap-2">
                @if(in_array(Auth::user()->role, ['administrator', 'moderator', 'budgeteer']))
                    <button @click="activeTab = 'accomplishments'" 
                            :class="activeTab === 'accomplishments' ? 'border-bottom border-3 border-success text-success fw-bold bg-success bg-opacity-5' : 'text-secondary hover-text-white'"
                            class="px-4 py-3 small text-uppercase tracking-widest btn btn-link text-decoration-none transition focus-ring focus-ring-success rounded-0">
                        Progress Logs
                    </button>
                @endif
                @if(in_array(Auth::user()->role, ['administrator', 'budgeteer']))
                    <button @click="activeTab = 'budgets'" 
                            :class="activeTab === 'budgets' ? 'border-bottom border-3 border-info text-info fw-bold bg-info bg-opacity-5' : 'text-secondary hover-text-white'"
                            class="px-4 py-3 small text-uppercase tracking-widest btn btn-link text-decoration-none transition focus-ring focus-ring-info rounded-0">
                        Budget Allocations
                    </button>
                @endif
            </div>

            <!-- Tab 1: Progress / Accomplishment logs -->
            @if(in_array(Auth::user()->role, ['administrator', 'moderator', 'budgeteer']))
                <div x-show="activeTab === 'accomplishments'" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
                    
                    <!-- Log Accomplishment Card -->
                    <div class="card bg-dark border border-secondary border-opacity-25 rounded-2 p-4 mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="h5 fw-bold text-white mb-0">Log Accomplishment</h4>
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-1 px-3 py-2 fw-bold text-uppercase small" style="font-size: 0.75rem;">Accomplishment Registry</span>
                        </div>
                        <form action="{{ route('sub-accounts.accomplishments.store', $subAccount) }}" method="POST">
                            @csrf
                            
                            <div class="row g-4 mb-4">
                                <div class="col-md-4">
                                    <label for="quantity" class="form-label text-secondary small fw-bold text-uppercase tracking-wider">Accomplished Quantity{{ $subAccount->unit ? ' (' . $subAccount->unit . ')' : '' }}</label>
                                    <input type="number" name="quantity" id="quantity" step="0.01" min="0.01" required placeholder="0.00" class="form-control bg-dark border-secondary text-white rounded-1 p-3 focus-ring focus-ring-success @error('quantity') is-invalid @enderror" autofocus>
                                    @error('quantity')
                                        <div class="text-danger small fw-bold mt-2">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="date_at" class="form-label text-secondary small fw-bold text-uppercase tracking-wider">Date of Accomplishment</label>
                                    <input type="date" name="date_at" id="date_at" required value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" class="form-control bg-dark border-secondary text-white rounded-1 p-3 focus-ring focus-ring-success @error('date_at') is-invalid @enderror">
                                    @error('date_at')
                                        <div class="text-danger small fw-bold mt-2">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="submit" class="btn btn-success px-4 py-3 rounded-1 fw-bold small text-uppercase tracking-wider shadow-sm w-100 transition duration-150" style="cursor: pointer;">
                                        Register Accomplishment
                                    </button>
                                </div>
                            </div>
                        </form>

                        <!-- Accomplishment History -->
                        <div class="mt-4 card bg-dark border border-secondary border-opacity-25 rounded-2 overflow-hidden">
                            <div class="card-header bg-secondary bg-opacity-5 border-bottom border-secondary border-opacity-25 p-4">
                                <h3 class="h5 fw-bold text-white mb-0">Accomplishment History</h3>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-dark table-hover mb-0">
                                        <thead>
                                            <tr class="bg-secondary bg-opacity-5">
                                                <th class="px-4 py-3 text-uppercase small fw-bold text-secondary tracking-wider">Date</th>
                                                <th class="px-4 py-3 text-uppercase small fw-bold text-secondary tracking-wider text-end">Quantity</th>
                                                <th class="px-4 py-3 text-uppercase small fw-bold text-secondary tracking-wider text-end">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($subAccount->accomplishments()->orderBy('date_at', 'desc')->orderBy('created_at', 'desc')->get() as $acc)
                                                <tr>
                                                    <td class="px-4 py-3 align-middle">
                                                        <span class="text-white small">{{ $acc->date_at->format('M d, Y') }}</span>
                                                    </td>
                                                    <td class="px-4 py-3 align-middle text-end font-monospace fw-bold text-success">
                                                        {{ number_format($acc->quantity, 2) }} {{ $subAccount->unit }}
                                                    </td>
                                                    <td class="px-4 py-3 align-middle text-end">
                                                        @if(Auth::user()->role === 'administrator')
                                                            <form action="{{ route('sub-accounts.accomplishments.destroy', $acc) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-link text-danger p-2 rounded-circle" onclick="return confirm('Are you sure you want to delete this accomplishment entry?')" title="Delete Entry" style="cursor: pointer;">
                                                                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                                </button>
                                                            </form>
                                                        @else
                                                            —
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="px-4 py-5 text-center text-secondary">
                                                        <svg class="mb-2" width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                        <h3 class="h6 text-white mb-0">No accomplishment history found</h3>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Tab 2: Budgets / Allocations -->
            @if(in_array(Auth::user()->role, ['administrator', 'budgeteer']))
                <div x-show="activeTab === 'budgets'" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
                    
                    <!-- Allocate Budget Form Card -->
                    <div class="card bg-dark border border-secondary border-opacity-25 rounded-2 p-4 mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="h5 fw-bold text-white mb-0">Allocate New Budget</h4>
                            <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-1 px-3 py-2 fw-bold text-uppercase small" style="font-size: 0.75rem;">Pending Approval</span>
                        </div>
                        <form action="{{ route('account-budgets.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="sub_account_id" value="{{ $subAccount->id }}">
                            
                            <div class="row g-4 mb-4">
                                <div class="col-md-6">
                                    <label for="budget_quantity" class="form-label text-secondary small fw-bold text-uppercase tracking-wider">Budget Quantity (Liters)</label>
                                    <input type="number" name="budget_quantity" id="budget_quantity" step="0.01" required placeholder="0.00" class="form-control bg-dark border-secondary text-white rounded-1 p-3 focus-ring focus-ring-info @error('budget_quantity') is-invalid @enderror">
                                    @error('budget_quantity')
                                        <div class="text-danger small fw-bold mt-2">{{ $message }}</div>
                                    @enderror
                                    <!-- Dynamic Budget Limits Context -->
                                    <div class="form-text text-secondary mt-2">
                                        Current remaining unspent budget for this sub-account: <span class="text-white font-monospace fw-bold">{{ number_format($subAccount->remainingBudget(), 2) }} L</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="remarks" class="form-label text-secondary small fw-bold text-uppercase tracking-wider">Remarks</label>
                                    <input type="text" name="remarks" id="remarks" placeholder="Optional notes..." class="form-control bg-dark border-secondary text-white rounded-1 p-3 focus-ring focus-ring-info @error('remarks') is-invalid @enderror">
                                    @error('remarks')
                                        <div class="text-danger small fw-bold mt-2">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-end pt-1 pb-3">
                                <button type="submit" class="btn btn-info px-4 py-2 rounded-1 fw-bold small text-uppercase tracking-wider shadow-sm transition duration-150" style="cursor: pointer;">
                                    Submit for Approval
                                </button>
                            </div>
                        </form>

                        <!-- Budget History -->
                        <div class="mt-4 card bg-dark border border-secondary border-opacity-25 rounded-2 overflow-hidden">
                            <div class="card-header bg-secondary bg-opacity-5 border-bottom border-secondary border-opacity-25 p-4">
                                <h3 class="h5 fw-bold text-white mb-0">Budget Allocation History</h3>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-dark table-hover mb-0">
                                        <thead>
                                            <tr class="bg-secondary bg-opacity-5">
                                                <th class="px-4 py-3 text-uppercase small fw-bold text-secondary tracking-wider">Date</th>
                                                <th class="px-4 py-3 text-uppercase small fw-bold text-secondary tracking-wider text-end">Quantity</th>
                                                <th class="px-4 py-3 text-uppercase small fw-bold text-secondary tracking-wider text-center">Status</th>
                                                <th class="px-4 py-3 text-uppercase small fw-bold text-secondary tracking-wider">Remarks</th>
                                                <th class="px-4 py-3 text-uppercase small fw-bold text-secondary tracking-wider text-end">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($subAccount->budgets()->orderBy('created_at', 'desc')->get() as $budget)
                                                <tr>
                                                    <td class="px-4 py-3 align-middle">
                                                        <span class="text-white small">{{ $budget->created_at->format('M d, Y') }}</span>
                                                        <span class="d-block text-secondary smaller fw-bold text-uppercase" style="font-size: 0.75rem;">{{ $budget->created_at->format('h:i A') }}</span>
                                                    </td>
                                                    <td class="px-4 py-3 align-middle text-end font-monospace fw-bold text-info">
                                                        {{ number_format($budget->budget_quantity, 2) }} L
                                                    </td>
                                                    <td class="px-4 py-3 align-middle text-center">
                                                        <span class="badge rounded-1 fw-bold text-uppercase smaller px-3 py-2
                                                            {{ $budget->status === 'Approved' ? 'bg-success bg-opacity-10 text-success border border-success border-opacity-25' : 
                                                            ($budget->status === 'Rejected' ? 'bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25' : 'bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25') }}">
                                                            {{ $budget->status }}
                                                        </span>
                                                    </td>
                                                    <td class="px-4 py-3 align-middle small text-secondary">
                                                        {{ Str::limit($budget->remarks, 50) ?: '—' }}
                                                    </td>
                                                    <td class="px-4 py-3 align-middle text-end">
                                                        <a href="{{ route('account-budgets.show', $budget) }}" class="btn btn-link text-info p-2 rounded-circle" title="View Budget Details" style="cursor: pointer;">
                                                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="px-4 py-5 text-center text-secondary">
                                                        <svg class="mb-2" width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                        <h3 class="h6 text-white mb-0">No budget history found</h3>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
