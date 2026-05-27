<x-app-layout>
    <x-slot name="header">
        <h2 class="h2 fw-bold text-light mb-0">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-5">
        <div class="container-xl">
            <div class="vstack gap-5">
                <!-- Welcome Section -->
                <div class="position-relative overflow-hidden rounded-4 p-4 p-md-5 shadow-lg" style="background-color: #D0BCFF;">
                    <div class="position-relative z-1">
                        <h3 class="display-5 fw-bold text-dark mb-2 tracking-tight">
                            Good day, {{ Auth::user()->name }}
                        </h3>
                        <p class="text-dark text-opacity-75 fs-5 fw-medium mb-4 mb-md-5" style="max-width: 600px;">
                            Monitor and manage your fleet utilization with precision and ease.
                        </p>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge rounded-pill px-3 py-2 fw-bold text-uppercase tracking-widest shadow-sm" style="font-size: 0.65rem; color: #D0BCFF; background-color: #381E72;">
                                {{ Auth::user()->role }}
                            </span>
                        </div>
                    </div>
                    <!-- Decorative element -->
                    <div class="position-absolute top-0 end-0 mt-n5 me-n5 bg-white bg-opacity-25 rounded-circle" style="width: 320px; height: 320px; filter: blur(80px);"></div>
                    <div class="position-absolute bottom-0 end-0 mb-4 me-5 d-none d-lg-block opacity-25">
                        <x-application-logo class="fill-current text-dark" style="width: 200px; height: 200px;" />
                    </div>
                </div>

                <!-- Action Cards Grid -->
                <div class="row g-4">
                    <!-- Assets Card -->
                    <div class="col-12 col-md-6 col-lg-4">
                        <a href="{{ route('assets.index') }}" class="card h-100 bg-dark border-secondary border-opacity-25 rounded-4 p-4 text-decoration-none shadow-sm transition-all hover-opacity">
                            <div class="card-body d-flex flex-column justify-content-between p-0">
                                <div>
                                    <div class="rounded-3 d-flex align-items-center justify-content-center mb-4 shadow-sm" style="width: 64px; height: 64px; background-color: #D0BCFF;">
                                        <svg style="width: 32px; height: 32px; color: #381E72;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                                        </svg>
                                    </div>
                                    <h4 class="h5 fw-bold text-light mb-2">Fleet</h4>
                                    <p class="text-secondary small mb-4">Access the full catalog of registered assets and equipment.</p>
                                </div>
                                <div class="mt-4 d-flex align-items-center fw-bold small text-uppercase tracking-widest" style="color: #D0BCFF;">
                                    Browse Fleet
                                    <svg class="ms-2" style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- User Management Card -->
                    @if(in_array(Auth::user()->role, ['administrator', 'moderator']))
                    <div class="col-12 col-md-6 col-lg-4">
                        <a href="{{ route('users.index') }}" class="card h-100 bg-dark border-secondary border-opacity-25 rounded-4 p-4 text-decoration-none shadow-sm transition-all hover-opacity">
                            <div class="card-body d-flex flex-column justify-content-between p-0">
                                <div>
                                    <div class="rounded-3 d-flex align-items-center justify-content-center mb-4 shadow-sm" style="width: 64px; height: 64px; background-color: #A8EFF2;">
                                        <svg style="width: 32px; height: 32px; color: #003739;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                        </svg>
                                    </div>
                                    <h4 class="h5 fw-bold text-light mb-2">Users</h4>
                                    <p class="text-secondary small mb-4">Manage permissions, roles, and security for system users.</p>
                                </div>
                                <div class="mt-4 d-flex align-items-center fw-bold small text-uppercase tracking-widest" style="color: #A8EFF2;">
                                    Manage Users
                                    <svg class="ms-2" style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                                </div>
                            </div>
                        </a>
                    </div>
                    @endif

                    <!-- Asset Types Card -->
                    @if(Auth::user()->role === 'administrator')
                    <div class="col-12 col-md-6 col-lg-4">
                        <a href="{{ route('asset-types.index') }}" class="card h-100 bg-dark border-secondary border-opacity-25 rounded-4 p-4 text-decoration-none shadow-sm transition-all hover-opacity">
                            <div class="card-body d-flex flex-column justify-content-between p-0">
                                <div>
                                    <div class="rounded-3 d-flex align-items-center justify-content-center mb-4 shadow-sm" style="width: 64px; height: 64px; background-color: #FFDCC0;">
                                        <svg style="width: 32px; height: 32px; color: #2F1500;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                        </svg>
                                    </div>
                                    <h4 class="h5 fw-bold text-light mb-2">Classification</h4>
                                    <p class="text-secondary small mb-4">Define equipment categories and utilization factors.</p>
                                </div>
                                <div class="mt-4 d-flex align-items-center fw-bold small text-uppercase tracking-widest" style="color: #FFDCC0;">
                                    Configure Types
                                    <svg class="ms-2" style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                                </div>
                            </div>
                        </a>
                    </div>
                    @endif

                    <!-- Chargeable Accounts Card -->
                    @if(in_array(Auth::user()->role, ['administrator', 'moderator']))
                    <div class="col-12 col-md-6 col-lg-4">
                        <a href="{{ route('chargeable-accounts.index') }}" class="card h-100 bg-dark border-secondary border-opacity-25 rounded-4 p-4 text-decoration-none shadow-sm transition-all hover-opacity">
                            <div class="card-body d-flex flex-column justify-content-between p-0">
                                <div>
                                    <div class="rounded-3 d-flex align-items-center justify-content-center mb-4 shadow-sm" style="width: 64px; height: 64px; background-color: #C4E1F6;">
                                        <svg style="width: 32px; height: 32px; color: #001D39;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                        </svg>
                                    </div>
                                    <h4 class="h5 fw-bold text-light mb-2">Accounts</h4>
                                    <p class="text-secondary small mb-4">Manage chargeable accounts and configure their status.</p>
                                </div>
                                <div class="mt-4 d-flex align-items-center fw-bold small text-uppercase tracking-widest" style="color: #C4E1F6;">
                                    Manage Accounts
                                    <svg class="ms-2" style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                                </div>
                            </div>
                        </a>
                    </div>
                    @endif

                    <!-- Budgets Card -->
                    @if(in_array(Auth::user()->role, ['administrator', 'budgeteer']))
                    <div class="col-12 col-md-6 col-lg-4">
                        <a href="{{ route('account-budgets.index') }}" class="card h-100 bg-dark border-secondary border-opacity-25 rounded-4 p-4 text-decoration-none shadow-sm transition-all hover-opacity">
                            <div class="card-body d-flex flex-column justify-content-between p-0">
                                <div>
                                    <div class="rounded-3 d-flex align-items-center justify-content-center mb-4 shadow-sm" style="width: 64px; height: 64px; background-color: #D0BCFF;">
                                        <svg style="width: 32px; height: 32px; color: #381E72;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <h4 class="h5 fw-bold text-light mb-2">Budgets</h4>
                                    <p class="text-secondary small mb-4">Allocate and monitor fuel budget quantities per chargeable account.</p>
                                </div>
                                <div class="mt-4 d-flex align-items-center fw-bold small text-uppercase tracking-widest" style="color: #D0BCFF;">
                                    Manage Budgets
                                    <svg class="ms-2" style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                                </div>
                            </div>
                        </a>
                    </div>
                    @endif

                    <!-- Fuel Orders Card -->
                    <div class="col-12 col-md-6 col-lg-4">
                        <a href="{{ route('fuel-orders.index') }}" class="card h-100 bg-dark border-secondary border-opacity-25 rounded-4 p-4 text-decoration-none shadow-sm transition-all hover-opacity">
                            <div class="card-body d-flex flex-column justify-content-between p-0">
                                <div>
                                    <div class="rounded-3 d-flex align-items-center justify-content-center mb-4 shadow-sm" style="width: 64px; height: 64px; background-color: #F2B8B5;">
                                        <svg style="width: 32px; height: 32px; color: #601410;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <h4 class="h5 fw-bold text-light mb-2">Fuel Orders</h4>
                                    <p class="text-secondary small mb-4">Issue and manage fuel replenishment orders for assets.</p>
                                </div>
                                <div class="mt-4 d-flex align-items-center fw-bold small text-uppercase tracking-widest" style="color: #F2B8B5;">
                                    {{ in_array(Auth::user()->role, ['data_logger', 'data logger', 'administrator']) ? 'Issue New Order' : 'View Orders' }}
                                    <svg class="ms-2" style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- Budget Dashboard Card -->
                    <div class="col-12 col-md-6 col-lg-4">
                        <a href="{{ route('dashboard.account-budgets') }}" class="card h-100 bg-dark border-secondary border-opacity-25 rounded-4 p-4 text-decoration-none shadow-sm transition-all hover-opacity">
                            <div class="card-body d-flex flex-column justify-content-between p-0">
                                <div>
                                    <div class="rounded-3 d-flex align-items-center justify-content-center mb-4 shadow-sm" style="width: 64px; height: 64px; background-color: #82C8B5;">
                                        <svg style="width: 32px; height: 32px; color: #053D2A;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                                        </svg>
                                    </div>
                                    <h4 class="h5 fw-bold text-light mb-2">Budget Dashboard</h4>
                                    <p class="text-secondary small mb-4">Visual dashboard showing budget vs consumption data.</p>
                                </div>
                                <div class="mt-4 d-flex align-items-center fw-bold small text-uppercase tracking-widest" style="color: #82C8B5;">
                                    View Dashboard
                                    <svg class="ms-2" style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .hover-opacity:hover {
            opacity: 0.85;
        }
    </style>
</x-app-layout>
