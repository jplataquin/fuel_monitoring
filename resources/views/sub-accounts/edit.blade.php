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

                    <div class="d-flex align-items-center justify-content-end pt-4 border-top border-secondary border-opacity-25 gap-3">
                        <a href="{{ route('chargeable-accounts.show', $subAccount->chargeableAccount) }}" class="btn btn-link text-secondary text-decoration-none small fw-bold text-uppercase tracking-widest me-3">Cancel</a>
                        <button type="submit" class="btn btn-primary px-5 py-3 rounded-pill fw-bold small text-uppercase tracking-wider shadow">
                            {{ __('Update') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
