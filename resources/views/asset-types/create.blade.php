<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-bold text-light mb-0">
            {{ __('New Classification') }}
        </h2>
    </x-slot>

    <div class="container-xl py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card bg-dark border-secondary border-opacity-25 shadow-lg">
                    <div class="card-body p-4 p-md-5">
                        <form method="POST" action="{{ route('asset-types.store') }}">
                            @csrf

                            <div class="mb-4">
                                <label for="name" class="form-label text-secondary text-uppercase small fw-bold tracking-widest ps-1 mb-2">
                                    {{ __('Classification Name') }}
                                </label>
                                <input id="name" name="name" type="text" 
                                    class="form-control form-control-lg bg-dark bg-opacity-25 border-secondary border-opacity-50 text-light focus-ring focus-ring-primary" 
                                    value="{{ old('name') }}" required autofocus 
                                    placeholder="e.g. Service Vehicle, Heavy Equipment">
                                @if($errors->has('name'))
                                    <div class="text-danger small fw-bold mt-2 ps-1">
                                        {{ $errors->first('name') }}
                                    </div>
                                @endif
                            </div>

                            <div class="d-flex align-items-center justify-content-end pt-4 mt-4 border-top border-secondary border-opacity-25 gap-3">       
                                <a href="{{ route('asset-types.index') }}" class="btn btn-link text-secondary text-uppercase small fw-bold tracking-widest text-decoration-none px-4">
                                    Cancel
                                </a>
                                <button type="submit" class="btn btn-primary rounded-pill px-5 py-3 fw-bold text-uppercase tracking-widest shadow-sm">
                                    {{ __('Create') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
