<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-weight-bold text-light mb-0">
            {{ __('Update Access') }}: {{ $user->name }}
        </h2>
    </x-slot>

    <div class="py-5">
        <div class="container" style="max-width: 800px;">
            <div class="row g-4">
                <!-- User Information Section -->
                <div class="col-12">
                    <div class="card bg-dark border-secondary shadow-lg rounded-4 p-4">
                        <h3 class="small fw-bold text-primary text-uppercase tracking-widest mb-4 d-flex align-items-center">
                            <span class="bg-primary opacity-25 me-3" style="width: 32px; height: 1px;"></span>
                            Identity Information
                        </h3>
                        <form method="POST" action="{{ route('users.update', $user) }}">
                            @csrf
                            @method('PATCH')

                            <div class="mb-4">
                                <label for="name" class="form-label small fw-bold text-secondary text-uppercase tracking-widest">{{ __('Full Name') }}</label>
                                <input id="name" name="name" type="text" class="form-control bg-dark text-light border-secondary p-3 rounded-3" value="{{ old('name', $user->name) }}" required autofocus>
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>

                            <div class="mb-4">
                                <label for="email" class="form-label small fw-bold text-secondary text-uppercase tracking-widest">{{ __('Email Address') }}</label>
                                <input id="email" name="email" type="email" class="form-control bg-dark text-light border-secondary p-3 rounded-3 font-monospace" value="{{ old('email', $user->email) }}" required>
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            </div>

                            @if(Auth::user()->role === 'administrator')
                            <div class="mb-4">
                                <label for="role" class="form-label small fw-bold text-secondary text-uppercase tracking-widest">{{ __('System Role') }}</label>
                                <select id="role" name="role" class="form-select bg-dark text-light border-secondary p-3 rounded-3">
                                    <option value="administrator" {{ old('role', $user->role) === 'administrator' ? 'selected' : '' }}>Administrator</option>
                                    <option value="moderator" {{ old('role', $user->role) === 'moderator' ? 'selected' : '' }}>Moderator</option>
                                    <option value="data_logger" {{ old('role', $user->role) === 'data_logger' ? 'selected' : '' }}>Data Logger</option>
                                    <option value="fuel_man" {{ old('role', $user->role) === 'fuel_man' ? 'selected' : '' }}>Fuel Man</option>
                                    <option value="budgeteer" {{ old('role', $user->role) === 'budgeteer' ? 'selected' : '' }}>Budgeteer</option>
                                </select>
                                <x-input-error :messages="$errors->get('role')" class="mt-2" />
                            </div>
                            @endif

                            <div class="d-flex justify-content-end pt-4 border-top border-secondary">
                                <button type="submit" class="btn btn-primary rounded-pill px-5 py-3 fw-bold small text-uppercase tracking-widest shadow-sm">
                                    {{ __('Update') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Password Reset Section -->
                <div class="col-12">
                    <div class="card bg-dark border-danger border-opacity-25 shadow-lg rounded-4 p-4">
                        <h3 class="small fw-bold text-danger text-uppercase tracking-widest mb-4 d-flex align-items-center">
                            <span class="bg-danger opacity-25 me-3" style="width: 32px; height: 1px;"></span>
                            Security Override
                        </h3>
                        <form method="POST" action="{{ route('users.reset-password', $user) }}">
                            @csrf

                            <div class="mb-4">
                                <label for="password" class="form-label small fw-bold text-secondary text-uppercase tracking-widest">{{ __('New Temporary Password') }}</label>
                                <input id="password" name="password" type="text" class="form-control bg-dark text-light border-danger border-opacity-25 p-3 rounded-3 font-monospace" required placeholder="Issue a new password">
                                <x-input-error :messages="$errors->get('password')" class="mt-2" />
                            </div>

                            <div class="d-flex justify-content-end pt-4 border-top border-secondary">
                                <button type="submit" class="btn btn-danger rounded-pill px-5 py-3 fw-bold small text-uppercase tracking-widest shadow-sm">
                                    {{ __('Reset Password') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
