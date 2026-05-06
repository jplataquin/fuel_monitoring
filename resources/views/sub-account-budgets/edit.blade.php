<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-weight-bold text-white mb-0">
            {{ __('Edit Sub Account Budget') }}
        </h2>
    </x-slot>

    <div class="py-5">
        <div class="container" style="max-width: 600px;">
            <div class="card bg-dark border-secondary shadow-lg rounded-4 p-4">
                <form method="POST" action="{{ route('account-budgets.update', $accountBudget) }}">
                    @csrf
                    @method('PATCH')

                    <div class="mb-4">
                        <label for="sub_account_id" class="form-label text-secondary small fw-bold text-uppercase tracking-wider">Sub Account</label>
                        <select id="sub_account_id" name="sub_account_id" class="form-select bg-dark border-secondary text-white rounded-3 p-3 focus-ring focus-ring-primary" required>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}" {{ old('sub_account_id', $accountBudget->sub_account_id) == $account->id ? 'selected' : '' }}>
                                    {{ $account->name }} ({{ $account->chargeableAccount->name }})
                                </option>
                            @endforeach
                        </select>
                        @error('sub_account_id')
                            <div class="text-danger small mt-2 fw-bold">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="budget_quantity" class="form-label text-secondary small fw-bold text-uppercase tracking-wider">Budget Quantity (Liters)</label>
                        <input id="budget_quantity" name="budget_quantity" type="number" step="0.01" class="form-control bg-dark border-secondary text-white rounded-3 p-3 focus-ring focus-ring-primary" value="{{ old('budget_quantity', $accountBudget->budget_quantity) }}" required placeholder="0.00">
                        @error('budget_quantity')
                            <div class="text-danger small mt-2 fw-bold">{{ $message }}</div>
                        @enderror
                    </div>

                    @if(in_array(Auth::user()->role, ['administrator', 'moderator']))
                        <div class="mb-4">
                            <label for="status" class="form-label text-secondary small fw-bold text-uppercase tracking-wider">Status</label>
                            <select id="status" name="status" class="form-select bg-dark border-secondary text-white rounded-3 p-3 focus-ring focus-ring-primary">
                                <option value="Pending" {{ old('status', $accountBudget->status) == 'Pending' ? 'selected' : '' }}>Pending</option>
                                <option value="Approved" {{ old('status', $accountBudget->status) == 'Approved' ? 'selected' : '' }}>Approved</option>
                                <option value="Rejected" {{ old('status', $accountBudget->status) == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                            @error('status')
                                <div class="text-danger small mt-2 fw-bold">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif

                    <div class="mb-4">
                        <label for="remarks" class="form-label text-secondary small fw-bold text-uppercase tracking-wider">Remarks</label>
                        <textarea id="remarks" name="remarks" class="form-control bg-dark border-secondary text-white rounded-3 p-3 focus-ring focus-ring-primary h-32" placeholder="Optional notes..." style="height: 120px;">{{ old('remarks', $accountBudget->remarks) }}</textarea>
                        @error('remarks')
                            <div class="text-danger small mt-2 fw-bold">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex align-items-center justify-content-end pt-4 border-top border-secondary border-opacity-25 gap-3">
                        <a href="{{ route('account-budgets.index') }}" class="btn btn-link text-secondary text-decoration-none small fw-bold text-uppercase tracking-widest me-3">Cancel</a>
                        <button type="submit" class="btn btn-primary px-5 py-3 rounded-pill fw-bold small text-uppercase tracking-wider shadow">
                            {{ __('UPDATE') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
