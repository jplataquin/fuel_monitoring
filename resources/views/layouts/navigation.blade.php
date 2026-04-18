<nav x-data="{ open: false }" class="bg-[#1C1B1F] border-b border-[#49454F] sticky top-0 z-[100]">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-20">
            <div class="flex items-center">
                <!-- Hamburger (Mobile) -->
                <div class="flex items-center sm:hidden mr-4">
                    <button @click="open = ! open" type="button" class="inline-flex items-center justify-center p-2.5 rounded-full text-[#CAC4D0] hover:bg-[#49454F] focus:outline-none transition duration-150 ease-in-out">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 group gap-x-4">
                        <div class="bg-[#D0BCFF] p-2 rounded-xl group-hover:bg-[#EADDFF] transition-colors">
                            <x-application-logo class="block h-6 w-auto fill-current text-[#381E72]" />
                        </div>
                        <span class="text-xl font-black tracking-tight text-[#E6E1E5] hidden lg:block">Fuel Monitoring</span>
                    </a>
                </div>
            </div>

            <!-- Navigation Links (Desktop) -->
            <div class="hidden sm:flex sm:items-center sm:ms-10 sm:space-x-8 gap-x-8">
                <x-nav-link :href="route('assets.index')" :active="request()->routeIs('assets.*')">
                    {{ __('Fleet') }}
                </x-nav-link>
                <x-nav-link :href="route('fuel-orders.index')" :active="request()->routeIs('fuel-orders.*')">
                    {{ __('Fuel Orders') }}
                </x-nav-link>

                <!-- Reports Dropdown -->
                <div class="inline-flex items-center">
                    <x-dropdown align="left" width="48" contentClasses="py-1 bg-[#2D2930] border border-[#49454F] rounded-md shadow-xl">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-bold uppercase tracking-widest text-[#CAC4D0] hover:text-[#E6E1E5] focus:outline-none transition-colors h-20">
                                <span>{{ __('Reports') }}</span>
                                <svg class="ms-1 h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('reports.asset-utilization')">
                                {{ __('Asset Utilization') }}
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('reports.fuel-orders')">
                                {{ __('Fuel Orders Summary') }}
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('reports.chargeable-accounts')">
                                {{ __('Chargeable Accounts') }}
                            </x-dropdown-link>
                        </x-slot>
                    </x-dropdown>
                </div>

                @if(Auth::user()->role === 'administrator')
                <x-nav-link :href="route('asset-types.index')" :active="request()->routeIs('asset-types.*')">
                    {{ __('Classifications') }}
                </x-nav-link>
                @endif
                @if(in_array(Auth::user()->role, ['administrator', 'moderator', 'budgeteer']))
                <x-nav-link :href="route('chargeable-accounts.index')" :active="request()->routeIs('chargeable-accounts.*')">
                    {{ __('Accounts') }}
                </x-nav-link>
                @endif
                @if(in_array(Auth::user()->role, ['administrator', 'moderator']))
                <x-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">
                    {{ __('Users') }}
                </x-nav-link>
                @endif
                @if(in_array(Auth::user()->role, ['administrator', 'moderator', 'budgeteer']))
                <x-nav-link :href="route('account-budgets.index')" :active="request()->routeIs('account-budgets.*')">
                    {{ __('Budget') }}
                </x-nav-link>
                @endif
            </div>

            <!-- Settings Dropdown -->
            <div class="flex items-center sm:ms-6">
                <x-dropdown align="right" width="48" contentClasses="py-1 bg-[#2D2930] border border-[#49454F] rounded-md shadow-xl">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center group focus:outline-none transition ease-in-out duration-150">
                            <div class="w-10 h-10 rounded-full bg-[#2D2930] border border-[#49454F] flex items-center justify-center text-[#D0BCFF] font-black text-xs uppercase group-hover:border-[#D0BCFF] transition-colors">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="px-4 py-2 border-b border-[#49454F] mb-1">
                            <p class="text-xs text-[#CAC4D0] uppercase font-bold tracking-widest">Account</p>
                            <p class="text-sm font-semibold text-[#E6E1E5] truncate">{{ Auth::user()->name }}</p>
                        </div>
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();" class="text-[#F2B8B5]">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>
        </div>
    </div>

    <!-- Mobile Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-[#1C1B1F] border-t border-[#49454F]">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('assets.index')" :active="request()->routeIs('assets.*')">
                {{ __('Assets') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('fuel-orders.index')" :active="request()->routeIs('fuel-orders.*')">
                {{ __('Fuel Orders') }}
            </x-responsive-nav-link>
            
            <div class="px-4 py-2 mt-2 text-xs font-bold text-[#D0BCFF] uppercase tracking-widest border-t border-[#49454F]/50">Reports</div>
            <x-responsive-nav-link :href="route('reports.asset-utilization')" :active="request()->routeIs('reports.asset-utilization')" class="pl-8">
                {{ __('Asset Utilization') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('reports.fuel-orders')" :active="request()->routeIs('reports.fuel-orders')" class="pl-8">
                {{ __('Fuel Orders Summary') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('reports.chargeable-accounts')" :active="request()->routeIs('reports.chargeable-accounts')" class="pl-8">
                {{ __('Chargeable Accounts') }}
            </x-responsive-nav-link>

            @if(Auth::user()->role === 'administrator')
            <x-responsive-nav-link :href="route('asset-types.index')" :active="request()->routeIs('asset-types.*')">
                {{ __('Asset Types') }}
            </x-responsive-nav-link>
            @endif
            @if(in_array(Auth::user()->role, ['administrator', 'moderator', 'budgeteer']))
            <x-responsive-nav-link :href="route('chargeable-accounts.index')" :active="request()->routeIs('chargeable-accounts.*')">
                {{ __('Chargeable Accounts') }}
            </x-responsive-nav-link>
            @endif
            @if(in_array(Auth::user()->role, ['administrator', 'moderator', 'budgeteer']))
            <x-responsive-nav-link :href="route('account-budgets.index')" :active="request()->routeIs('account-budgets.*')">
                {{ __('Budgets') }}
            </x-responsive-nav-link>
            @endif
            @if(in_array(Auth::user()->role, ['administrator', 'moderator']))
            <x-responsive-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">
                {{ __('Users') }}
            </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-[#49454F]">
            <div class="px-4 flex items-center space-x-3 mb-3">
                <div class="w-10 h-10 rounded-full bg-[#D0BCFF] flex items-center justify-center text-[#381E72] font-bold">
                    {{ substr(Auth::user()->name, 0, 1) }}
                </div>
                <div>
                    <div class="font-bold text-base text-[#E6E1E5]">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-[#CAC4D0]">{{ Auth::user()->email }}</div>
                </div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();" class="text-[#F2B8B5]">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
