<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-weight-bold text-white mb-0">
            {{ __('Update Sub-Account') }}
        </h2>
    </x-slot>

    <div class="py-5">
        <div class="container" style="max-width: 600px;">
            <div class="card bg-dark border-secondary shadow-lg rounded-4 p-4">
                <form method="POST" action="{{ route('sub-accounts.update', $subAccount) }}">
                    @csrf
                    @method('PATCH')

                    <div class="mb-4">
                        <label for="name" class="form-label text-secondary small fw-bold text-uppercase tracking-wider">Sub-Account Name</label>
                        <input id="name" name="name" type="text" class="form-control bg-dark border-secondary text-white rounded-3 p-3 focus-ring focus-ring-primary" value="{{ old('name', $subAccount->name) }}" required autofocus>
                        @error('name')
                            <div class="text-danger small mt-2 fw-bold">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="accomplishment" class="form-label text-secondary small fw-bold text-uppercase tracking-wider">Accomplishment (%)</label>
                        <input id="accomplishment" name="accomplishment" type="number" step="0.01" min="0" max="100" class="form-control bg-dark border-secondary text-white rounded-3 p-3 focus-ring focus-ring-primary" value="{{ old('accomplishment', $subAccount->accomplishment) }}" required>
                        @error('accomplishment')
                            <div class="text-danger small mt-2 fw-bold">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <div class="form-check form-switch p-3 bg-secondary bg-opacity-10 border border-secondary border-opacity-10 rounded-3 d-flex align-items-center justify-content-between">
                            <div class="flex-grow-1 pe-3">
                                <label class="form-check-label text-secondary small fw-bold text-uppercase tracking-wider d-block mb-1" style="cursor: pointer;" for="type_toggle">Uncontrolled Sub-Account 🔓</label>
                                <span class="text-secondary small d-block lh-sm" style="font-size: 0.8rem;">When enabled, this sub-account is exempt from overbudget controls and can go over budget without requiring a waiver.</span>
                            </div>
                            <input type="hidden" name="type" value="Controlled">
                            <input class="form-check-input ms-0" type="checkbox" role="switch" id="type_toggle" name="type" value="Uncontrolled" {{ old('type', $subAccount->type) === 'Uncontrolled' ? 'checked' : '' }} style="transform: scale(1.5); cursor: pointer; float: none;">
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-end pt-4 border-top border-secondary border-opacity-25 gap-3">
                        <a href="{{ route('chargeable-accounts.show', $subAccount->chargeableAccount) }}" class="btn btn-link text-secondary text-decoration-none small fw-bold text-uppercase tracking-widest me-3">Cancel</a>
                        <button type="submit" class="btn btn-primary px-5 py-3 rounded-pill fw-bold small text-uppercase tracking-wider shadow">
                            {{ __('Update') }}
                        </button>
                    </div>
                </form>
            </div>

            @if(Auth::user()->role === 'administrator')
                <div class="card bg-dark border-danger border-start border-4 shadow-lg rounded-4 p-4 mt-5">
                    <div class="mb-4">
                        <h3 class="h5 fw-bold text-danger mb-1">Merge Sub-Account</h3>
                        <p class="text-secondary small">Merge this sub-account into another. This action is irreversible. All associated fuel orders, utilization entries, and budgets will be retroactively moved to the selected target sub-account, and this sub-account will be soft-deleted.</p>
                    </div>

                    <form method="POST" action="{{ route('sub-accounts.merge', $subAccount) }}">
                        @csrf

                        <div class="mb-4">
                            <label for="merged_to_id" class="form-label text-secondary small fw-bold text-uppercase tracking-wider">Target Sub-Account (Merge Into)</label>
                            <select id="merged_to_id" name="merged_to_id" class="form-select bg-dark border-secondary text-white rounded-3 p-3 focus-ring focus-ring-danger" required>
                                <option value="" disabled selected>Select target sub-account...</option>
                                @foreach($subAccount->chargeableAccount->subAccounts->where('id', '!=', $subAccount->id) as $target)
                                    <option value="{{ $target->id }}">{{ $target->name }}</option>
                                @endforeach
                            </select>
                            @error('merged_to_id')
                                <div class="text-danger small mt-2 fw-bold">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="merge_remarks" class="form-label text-secondary small fw-bold text-uppercase tracking-wider">Merge Remarks</label>
                            <textarea id="merge_remarks" name="merge_remarks" rows="3" class="form-control bg-dark border-secondary text-white rounded-3 p-3 focus-ring focus-ring-danger" placeholder="Provide context or reason for this merge..." required>{{ old('merge_remarks') }}</textarea>
                            @error('merge_remarks')
                                <div class="text-danger small mt-2 fw-bold">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex align-items-center justify-content-end pt-4 border-top border-secondary border-opacity-25 gap-3">
                            <button type="submit" class="btn btn-danger px-5 py-3 rounded-pill fw-bold small text-uppercase tracking-wider shadow" onclick="return confirm('Are you sure you want to merge this sub-account? This will permanently reassign all its history and soft-delete this sub-account.');">
                                {{ __('Merge Sub-Account') }}
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
