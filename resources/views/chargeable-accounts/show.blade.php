<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 text-uppercase small fw-bold tracking-widest">
                        <li class="breadcrumb-item"><a href="{{ route('chargeable-accounts.index') }}" class="text-info text-decoration-none">Accounts</a></li>
                        <li class="breadcrumb-item active text-secondary" aria-current="page">{{ $chargeableAccount->name }}</li>
                    </ol>
                </nav>
            </div>
            @if(in_array(Auth::user()->role, ['administrator', 'moderator', 'budgeteer']))
                <div class="d-flex gap-2">
                    <a href="{{ route('chargeable-accounts.edit', $chargeableAccount) }}" class="btn btn-outline-secondary d-flex align-items-center rounded-pill px-4">
                        <svg class="me-2" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                        {{ __('Edit') }}
                    </a>
                </div>
            @endif
        </div>
    </x-slot>

    <div class="py-5">
        <div class="container" style="max-width: 900px;">
            <!-- Account Info -->
            <div class="card bg-dark border-secondary shadow-lg rounded-4 p-4 mb-5">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <h3 class="text-info small fw-bold text-uppercase tracking-widest mb-2">Name</h3>
                        <span class="h5 text-white fw-bold text-uppercase">{{ $chargeableAccount->name }}</span>
                    </div>
                    <div class="col-md-4 text-md-center">
                        <h3 class="text-info small fw-bold text-uppercase tracking-widest mb-2">Status</h3>
                        <span class="badge rounded-pill {{ $chargeableAccount->status === 'Active' ? 'bg-success' : 'bg-danger' }} px-3 py-2 fw-bold uppercase">
                            {{ $chargeableAccount->status }}
                        </span>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <h3 class="text-info small fw-bold text-uppercase tracking-widest mb-2">Total Sub-Accounts</h3>
                        <p class="display-6 fw-bold text-white mb-0">{{ $chargeableAccount->subAccounts->count() }}</p>
                    </div>
                </div>
            </div>

            @if(in_array(Auth::user()->role, ['administrator', 'moderator', 'budgeteer']))
                <!-- Add Sub-Account Form Card -->
                <div class="card bg-dark border-secondary border-start border-4 border-info shadow-lg rounded-4 p-4 mb-4">
                    <h4 class="h5 fw-bold text-white mb-4">Create Sub-Account</h4>
                    <form action="{{ route('chargeable-accounts.sub-accounts.store', $chargeableAccount) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <input type="text" name="name" placeholder="e.g. Project Alpha" required class="form-control bg-dark border-secondary text-white rounded-3 p-3 focus-ring focus-ring-info">
                            @if ($errors->any())
                                <div class="text-danger small fw-bold mt-2">
                                    <ul class="list-unstyled mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                        <div class="d-flex justify-content-end pt-3 border-top border-secondary border-opacity-25">
                            <button type="submit" class="btn btn-info px-4 py-2 rounded-pill fw-bold small text-uppercase tracking-wider shadow">
                                Add
                            </button>
                        </div>
                    </form>
                </div>
            @endif
            
            <!-- Offsets Section -->
            <div class="card bg-dark border-secondary shadow-lg rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-secondary bg-opacity-10 border-bottom border-secondary d-flex justify-content-between align-items-center p-3">
                    <h4 class="h5 fw-bold text-white mb-0">Initial Budget Offsets</h4>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover mb-0">
                            <thead>
                                <tr class="bg-secondary bg-opacity-10">
                                    <th class="px-4 py-3 text-uppercase small fw-bold text-secondary tracking-wider">Quantity</th>
                                    <th class="px-4 py-3 text-uppercase small fw-bold text-secondary tracking-wider">Remarks</th>
                                    <th class="px-4 py-3 text-uppercase small fw-bold text-secondary tracking-wider">Added By</th>
                                    <th class="px-4 py-3 text-uppercase small fw-bold text-secondary tracking-wider">Date</th>
                                    @if(Auth::user()->role === 'administrator')
                                        <th class="px-4 py-3 text-uppercase small fw-bold text-secondary tracking-wider text-end">Actions</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($chargeableAccount->offsets as $offset)
                                    <tr>
                                        <td class="px-4 py-3 align-middle font-monospace text-warning fw-bold">
                                            {{ number_format($offset->quantity, 2) }} L
                                        </td>
                                        <td class="px-4 py-3 align-middle text-light">
                                            {{ $offset->remarks ?: '-' }}
                                        </td>
                                        <td class="px-4 py-3 align-middle text-secondary small">
                                            {{ $offset->creator?->name ?? 'System' }}
                                        </td>
                                        <td class="px-4 py-3 align-middle text-secondary small">
                                            {{ $offset->created_at->format('M d, Y') }}
                                        </td>
                                        @if(Auth::user()->role === 'administrator')
                                            <td class="px-4 py-3 align-middle text-end">
                                                <form action="{{ route('chargeable-accounts.offsets.destroy', [$chargeableAccount, $offset]) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-link text-danger p-2 rounded-circle" onclick="return confirm('Are you sure you want to delete this offset?')" title="Delete Offset">
                                                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                    </button>
                                                </form>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-4 text-center text-secondary">
                                            <span class="small">No budget offsets recorded.</span>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @if(Auth::user()->role === 'administrator')
                <!-- Add Offset Form Card -->
                <div class="card bg-dark border-secondary border-start border-4 border-warning shadow-lg rounded-4 p-4 mb-4">
                    <h4 class="h5 fw-bold text-white mb-4">Add Budget Offset (Pre-System Consumption)</h4>
                    <form action="{{ route('chargeable-accounts.offsets.store', $chargeableAccount) }}" method="POST">
                        @csrf
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label for="quantity" class="form-label small fw-bold text-secondary text-uppercase tracking-wider">Quantity (Liters)</label>
                                <input type="number" step="0.01" name="quantity" id="quantity" placeholder="0.00" required class="form-control bg-dark border-secondary text-white rounded-3 p-3 focus-ring focus-ring-warning">
                            </div>
                            <div class="col-md-8">
                                <label for="remarks" class="form-label small fw-bold text-secondary text-uppercase tracking-wider">Remarks (Optional)</label>
                                <input type="text" name="remarks" id="remarks" placeholder="e.g. Disbursements before system go-live" class="form-control bg-dark border-secondary text-white rounded-3 p-3 focus-ring focus-ring-warning">
                            </div>
                        </div>
                        @if ($errors->any())
                            <div class="text-danger small fw-bold mt-2 mb-3">
                                <ul class="list-unstyled mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div class="d-flex justify-content-end pt-3 border-top border-secondary border-opacity-25">
                            <button type="submit" class="btn btn-warning px-4 py-2 rounded-pill fw-bold small text-uppercase tracking-wider shadow">
                                Add Offset
                            </button>
                        </div>
                    </form>
                </div>
            @endif
            
            <div class="card bg-dark border-secondary shadow-lg rounded-4 overflow-hidden">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover mb-0">
                            <thead>
                                <tr class="bg-secondary bg-opacity-10">
                                    <th class="px-4 py-3 text-uppercase small fw-bold text-secondary tracking-wider">Sub Accounts</th>
                                    <th class="px-4 py-3 text-uppercase small fw-bold text-secondary tracking-wider text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($chargeableAccount->subAccounts as $subAccount)
                                    <tr>
                                        <td class="px-4 py-3 align-middle">
                                            <div class="d-flex align-items-center">
                                                <svg class="text-secondary me-3" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                                <span class="text-light fw-medium">{{ $subAccount->name }}</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 align-middle text-end">
                                            <div class="btn-group">
                                                <a href="{{ route('sub-accounts.show', $subAccount) }}" class="btn btn-link text-info p-2 rounded-circle" title="View Sub-Account Details">
                                                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                                </a>
                                                @if(in_array(Auth::user()->role, ['administrator', 'moderator', 'budgeteer']))
                                                    <a href="{{ route('sub-accounts.edit', $subAccount) }}" class="btn btn-link text-warning p-2 rounded-circle" title="Edit Sub-Account">
                                                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                                    </a>
                                                    <form action="{{ route('sub-accounts.destroy', $subAccount) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-link text-danger p-2 rounded-circle" onclick="return confirm('Are you sure you want to delete this sub-account?')" title="Delete Sub-Account">
                                                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="px-4 py-5 text-center text-secondary">
                                            <svg class="mb-2" width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                            </svg>
                                            <h3 class="h6 text-white">No sub-accounts found</h3>
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
</x-app-layout>