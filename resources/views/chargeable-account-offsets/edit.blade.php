<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 text-uppercase small fw-bold tracking-widest">
                    <li class="breadcrumb-item"><a href="{{ route('chargeable-accounts.index') }}" class="text-info text-decoration-none">Accounts</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('chargeable-accounts.show', $chargeableAccount) }}" class="text-info text-decoration-none">{{ $chargeableAccount->name }}</a></li>
                    <li class="breadcrumb-item active text-secondary" aria-current="page">Edit Offset</li>
                </ol>
            </nav>
        </div>
    </x-slot>

    <div class="py-5">
        <div class="container" style="max-width: 700px;">
            <div class="card bg-dark border-secondary border-start border-4 border-warning shadow-lg rounded-4 p-4">
                <div class="d-flex align-items-center mb-4">
                    <div class="bg-warning bg-opacity-10 p-3 rounded-4 me-3">
                        <svg width="24" height="24" class="text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                    </div>
                    <div>
                        <h4 class="h5 fw-bold text-white mb-0">Edit Budget Offset</h4>
                        <p class="text-secondary small mb-0">Update the pre-system consumption record for {{ $chargeableAccount->name }}</p>
                    </div>
                </div>

                <form action="{{ route('chargeable-accounts.offsets.update', [$chargeableAccount, $offset]) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    
                    <div class="row g-4 mb-4">
                        <div class="col-12">
                            <label for="quantity" class="form-label small fw-bold text-secondary text-uppercase tracking-wider">Quantity (Liters)</label>
                            <input type="number" step="0.01" name="quantity" id="quantity" value="{{ old('quantity', $offset->quantity) }}" required class="form-control bg-dark border-secondary text-white rounded-3 p-3 focus-ring focus-ring-warning">
                            @error('quantity')
                                <div class="text-danger small fw-bold mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-12">
                            <label for="remarks" class="form-label small fw-bold text-secondary text-uppercase tracking-wider">Remarks</label>
                            <input type="text" name="remarks" id="remarks" value="{{ old('remarks', $offset->remarks) }}" placeholder="e.g. Disbursements before system go-live" class="form-control bg-dark border-secondary text-white rounded-3 p-3 focus-ring focus-ring-warning">
                            @error('remarks')
                                <div class="text-danger small fw-bold mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-4 border-top border-secondary border-opacity-25">
                        <a href="{{ route('chargeable-accounts.show', $chargeableAccount) }}" class="btn btn-link text-secondary text-decoration-none fw-bold small text-uppercase tracking-wider">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-warning px-5 py-2 rounded-pill fw-bold small text-uppercase tracking-wider shadow">
                            Update Offset
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
