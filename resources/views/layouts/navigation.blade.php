<nav class="navbar navbar-expand-lg navbar-dark bg-dark border-bottom border-secondary sticky-top py-3" x-data="{ open: false }">
    <div class="container-xl">
        <!-- Logo & Brand -->
        <a class="navbar-brand d-flex align-items-center gap-3 me-4" href="{{ route('dashboard') }}">
            <div class="bg-primary bg-opacity-25 p-2 rounded-3">
                <x-application-logo class="text-primary" style="height: 24px; width: auto;" />
            </div>
            <span class="fw-black text-uppercase tracking-tight d-none d-lg-inline-block" style="font-size: 1.25rem;">Fuel Monitoring</span>
        </a>

        <!-- Hamburger (Mobile) -->
        <button class="navbar-toggler border-0 shadow-none" type="button" @click="open = !open" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navigation Links -->
        <div class="collapse navbar-collapse" :class="{'show': open}" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 gap-1 gap-lg-3">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('assets.*') ? 'active fw-bold border-bottom border-primary' : '' }}" href="{{ route('assets.index') }}">{{ __('Fleet') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('fuel-orders.*') ? 'active fw-bold border-bottom border-primary' : '' }}" href="{{ route('fuel-orders.index') }}">
                        {{ __('Fuel Orders') }}
                        @php
                            $pendingCount = \App\Models\FuelOrder::where(function($q) {
                                $q->where('status', 'PEND')->orWhere('is_waiver_pending', true);
                            })->count();
                        @endphp
                        @if($pendingCount > 0)
                            <span class="badge rounded-pill bg-danger ms-1 align-middle font-monospace" style="font-size: 0.7rem; padding: 0.25em 0.55em;">
                                {{ $pendingCount > 99 ? '99+' : $pendingCount }}
                            </span>
                        @endif
                    </a>
                </li>

                <!-- Reports Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        {{ __('Reports') }}
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark shadow-lg border-secondary">
                        <li><a class="dropdown-item" href="{{ route('reports.asset-utilization') }}">{{ __('Asset Utilization') }}</a></li>
                        <li><a class="dropdown-item" href="{{ route('reports.fuel-orders') }}">{{ __('Fuel Orders Summary') }}</a></li>
                        <li><a class="dropdown-item" href="{{ route('reports.chargeable-accounts') }}">{{ __('Chargeable Accounts') }}</a></li>
                    </ul>
                </li>

                
                @if(in_array(Auth::user()->role, ['administrator', 'moderator', 'budgeteer']))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('chargeable-accounts.*') ? 'active fw-bold border-bottom border-primary' : '' }}" href="{{ route('chargeable-accounts.index') }}">{{ __('Accounts') }}</a>
                </li>
                @endif
                @if(in_array(Auth::user()->role, ['administrator', 'moderator', 'budgeteer']))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('account-budgets.*') ? 'active fw-bold border-bottom border-primary' : '' }}" href="{{ route('account-budgets.index') }}">{{ __('Budget') }}</a>
                </li>
                @endif

                

                @if(Auth::user()->role === 'administrator')
                
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('settings.*') || request()->routeIs('users.*') || request()->routeIs('asset-types.*') ? 'active fw-bold border-bottom border-primary' : '' }}" href="{{ route('settings.index') }}">{{ __('Settings') }}</a>
                </li>
                @endif
            </ul>

            <!-- Settings Dropdown -->
            <div class="d-flex align-items-center mt-3 mt-lg-0 ms-lg-3 pt-3 pt-lg-0 border-secondary border-lg-0">
                <div class="dropdown">
                    <button class="btn btn-link p-0 border-0 dropdown-toggle d-flex align-items-center text-decoration-none shadow-none" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="bg-secondary bg-opacity-25 border border-secondary rounded-circle d-flex align-items-center justify-content-center text-primary fw-black" style="width: 40px; height: 40px; font-size: 0.75rem;">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow-lg border-secondary mt-2">
                        <li class="px-3 py-2 border-bottom border-secondary mb-1">
                            <small class="text-secondary text-uppercase fw-bold tracking-widest" style="font-size: 0.65rem;">Account</small>
                            <p class="mb-0 fw-semibold text-truncate" style="max-width: 150px;">{{ Auth::user()->name }}</p>
                        </li>
                        <li><a class="dropdown-item" href="{{ route('profile.edit') }}">{{ __('Profile') }}</a></li>
                        <li><hr class="dropdown-divider border-secondary"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    {{ __('Log Out') }}
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</nav>
