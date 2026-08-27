<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-weight-bold text-white mb-0">
            {{ __('Update Chargeable Account') }}
        </h2>
    </x-slot>

    <div class="py-5">
        <div class="container" style="max-width: 600px;">
            <div class="card bg-dark border-secondary shadow-lg rounded-4 p-4">
                <form method="POST" action="{{ route('chargeable-accounts.update', $chargeableAccount) }}">
                    @csrf
                    @method('PATCH')

                    <div class="mb-4">
                        <label for="name" class="form-label text-secondary small fw-bold text-uppercase tracking-wider">Account Name</label>
                        <input id="name" name="name" type="text" class="form-control bg-dark border-secondary text-white rounded-3 p-3 focus-ring focus-ring-primary" value="{{ old('name', $chargeableAccount->name) }}" required autofocus pattern="^[^:]+$" title="Colons (:) are not allowed in the account name">
                        @error('name')
                            <div class="text-danger small mt-2 fw-bold">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-secondary small fw-bold text-uppercase tracking-wider">Classification</label>
                        <div class="d-flex gap-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="classification" id="running" value="Running" {{ old('classification', $chargeableAccount->classification) === 'Running' ? 'checked' : '' }} onchange="toggleDates()">
                                <label class="form-check-label text-white" for="running">Running</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="classification" id="scoped" value="Scoped" {{ old('classification', $chargeableAccount->classification) === 'Scoped' ? 'checked' : '' }} onchange="toggleDates()">
                                <label class="form-check-label text-white" for="scoped">Scoped</label>
                            </div>
                        </div>
                        @error('classification')
                            <div class="text-danger small mt-2 fw-bold">{{ $message }}</div>
                        @enderror
                    </div>

                    <div id="date-range-fields" class="{{ old('classification', $chargeableAccount->classification) === 'Scoped' ? '' : 'd-none' }}">
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="start_date" class="form-label text-secondary small fw-bold text-uppercase tracking-wider">Start Date</label>
                                <input id="start_date" name="start_date" type="date" class="form-control bg-dark border-secondary text-white rounded-3 p-3 focus-ring focus-ring-primary" value="{{ old('start_date', $chargeableAccount->start_date?->format('Y-m-d')) }}">
                                @error('start_date')
                                    <div class="text-danger small mt-2 fw-bold">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="end_date" class="form-label text-secondary small fw-bold text-uppercase tracking-wider">End Date</label>
                                <input id="end_date" name="end_date" type="date" class="form-control bg-dark border-secondary text-white rounded-3 p-3 focus-ring focus-ring-primary" value="{{ old('end_date', $chargeableAccount->end_date?->format('Y-m-d')) }}">
                                @error('end_date')
                                    <div class="text-danger small mt-2 fw-bold">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="status" class="form-label text-secondary small fw-bold text-uppercase tracking-wider">Status</label>
                        <select id="status" name="status" class="form-select bg-dark border-secondary text-white rounded-3 p-3 focus-ring focus-ring-primary">
                            <option value="Active" {{ old('status', $chargeableAccount->status) == 'Active' ? 'selected' : '' }}>Active</option>
                            <option value="Inactive" {{ old('status', $chargeableAccount->status) == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')
                            <div class="text-danger small mt-2 fw-bold">{{ $message }}</div>
                        @enderror
                    </div>

                    <script>
                        function toggleDates() {
                            const scoped = document.getElementById('scoped').checked;
                            const dateFields = document.getElementById('date-range-fields');
                            if (scoped) {
                                dateFields.classList.remove('d-none');
                            } else {
                                dateFields.classList.add('d-none');
                            }
                        }
                    </script>

                    <div class="d-flex align-items-center justify-content-end pt-4 border-top border-secondary border-opacity-25 gap-3">
                        <a href="{{ route('chargeable-accounts.index') }}" class="btn btn-link text-secondary text-decoration-none small fw-bold text-uppercase tracking-widest me-3">Cancel</a>
                        <button type="submit" class="btn btn-primary px-5 py-3 rounded-pill fw-bold small text-uppercase tracking-wider shadow">
                            {{ __('Update') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
