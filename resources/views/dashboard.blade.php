<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-[#E6E1E5] tracking-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="space-y-12">
                <!-- Welcome Section -->
                <div class="relative overflow-hidden bg-[#D0BCFF] rounded-[28px] p-10 md:p-14 shadow-2xl">
                    <div class="relative z-10">
                        <h3 class="text-3xl md:text-5xl font-bold text-[#381E72] mb-4 tracking-tight leading-tight">
                            Good day, {{ Auth::user()->name }}
                        </h3>
                        <p class="text-[#381E72]/80 text-lg font-medium mb-10 max-w-xl">
                            Monitor and manage your fleet utilization with precision and ease.
                        </p>
                        <div class="flex flex-wrap gap-3">
                            <span class="!px-5 !py-2 bg-[#381E72] text-[#D0BCFF] text-[10px] font-bold rounded-full uppercase tracking-[0.2em] shadow-lg">
                                {{ Auth::user()->role }}
                            </span>
                        </div>
                    </div>
                    <!-- Decorative element -->
                    <div class="absolute top-0 right-0 -mr-12 -mt-12 w-80 h-80 bg-[#EADDFF]/40 rounded-full blur-3xl"></div>
                    <div class="absolute bottom-0 right-0 mr-12 mb-12 hidden lg:block opacity-20 group">
                        <x-application-logo class="w-56 h-56 fill-current text-[#381E72] transform group-hover:rotate-12 transition-transform duration-700" />
                    </div>
                </div>

                <!-- Action Cards Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
                    <!-- Assets Card -->
                    <a href="{{ route('assets.index') }}" class="group bg-[#2D2930] hover:bg-[#49454F]/50 rounded-[28px] !p-10 transition-all duration-300 shadow-xl border border-[#49454F]/50 flex flex-col justify-between min-h-[260px]">
                        <div>
                            <div class="bg-[#D0BCFF] w-16 h-16 rounded-2xl flex items-center justify-center mb-8 shadow-lg group-hover:scale-110 transition-transform">
                                <svg class="w-8 h-8 text-[#381E72]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                                </svg>
                            </div>
                            <h4 class="text-xl font-bold text-[#E6E1E5] mb-3 tracking-tight">Fleet</h4>
                            <p class="text-[#CAC4D0] text-sm leading-relaxed font-medium">Access the full catalog of registered assets and equipment.</p>
                        </div>
                        <div class="mt-8 flex items-center text-[#D0BCFF] font-bold text-[10px] uppercase tracking-[0.2em]">
                            Browse Fleet
                            <svg class="w-4 h-4 ml-2 group-hover:translate-x-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </div>
                    </a>

                    <!-- User Management Card -->
                    @if(in_array(Auth::user()->role, ['administrator', 'moderator']))
                    <a href="{{ route('users.index') }}" class="group bg-[#2D2930] hover:bg-[#49454F]/50 rounded-[28px] !p-10 transition-all duration-300 shadow-xl border border-[#49454F]/50 flex flex-col justify-between min-h-[260px]">
                        <div>
                            <div class="bg-[#A8EFF2] w-16 h-16 rounded-2xl flex items-center justify-center mb-8 shadow-lg group-hover:scale-110 transition-transform">
                                <svg class="w-8 h-8 text-[#003739]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                            <h4 class="text-xl font-bold text-[#E6E1E5] mb-3 tracking-tight">Users</h4>
                            <p class="text-[#CAC4D0] text-sm leading-relaxed font-medium">Manage permissions, roles, and security for system users.</p>
                        </div>
                        <div class="mt-8 flex items-center text-[#A8EFF2] font-bold text-[10px] uppercase tracking-[0.2em]">
                            Manage Users
                            <svg class="w-4 h-4 ml-2 group-hover:translate-x-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </div>
                    </a>
                    @endif

                    <!-- Asset Types Card -->
                    @if(Auth::user()->role === 'administrator')
                    <a href="{{ route('asset-types.index') }}" class="group bg-[#2D2930] hover:bg-[#49454F]/50 rounded-[28px] !p-10 transition-all duration-300 shadow-xl border border-[#49454F]/50 flex flex-col justify-between min-h-[260px]">
                        <div>
                            <div class="bg-[#FFDCC0] w-16 h-16 rounded-2xl flex items-center justify-center mb-8 shadow-lg group-hover:scale-110 transition-transform">
                                <svg class="w-8 h-8 text-[#2F1500]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                            </div>
                            <h4 class="text-xl font-bold text-[#E6E1E5] mb-3 tracking-tight">Classification</h4>
                            <p class="text-[#CAC4D0] text-sm leading-relaxed font-medium">Define equipment categories and utilization factors.</p>
                        </div>
                        <div class="mt-8 flex items-center text-[#FFDCC0] font-bold text-[10px] uppercase tracking-[0.2em]">
                            Configure Types
                            <svg class="w-4 h-4 ml-2 group-hover:translate-x-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </div>
                    </a>
                    @endif

                    <!-- Chargeable Accounts Card -->
                    @if(in_array(Auth::user()->role, ['administrator', 'moderator']))
                    <a href="{{ route('chargeable-accounts.index') }}" class="group bg-[#2D2930] hover:bg-[#49454F]/50 rounded-[28px] !p-10 transition-all duration-300 shadow-xl border border-[#49454F]/50 flex flex-col justify-between min-h-[260px]">
                        <div>
                            <div class="bg-[#C4E1F6] w-16 h-16 rounded-2xl flex items-center justify-center mb-8 shadow-lg group-hover:scale-110 transition-transform">
                                <svg class="w-8 h-8 text-[#001D39]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                            </div>
                            <h4 class="text-xl font-bold text-[#E6E1E5] mb-3 tracking-tight">Accounts</h4>
                            <p class="text-[#CAC4D0] text-sm leading-relaxed font-medium">Manage chargeable accounts and configure their status.</p>
                        </div>
                        <div class="mt-8 flex items-center text-[#C4E1F6] font-bold text-[10px] uppercase tracking-[0.2em]">
                            Manage Accounts
                            <svg class="w-4 h-4 ml-2 group-hover:translate-x-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </div>
                    </a>
                    @endif

                    <!-- Budgets Card -->
                    @if(in_array(Auth::user()->role, ['administrator', 'budgeteer']))
                    <a href="{{ route('account-budgets.index') }}" class="group bg-[#2D2930] hover:bg-[#49454F]/50 rounded-[28px] !p-10 transition-all duration-300 shadow-xl border border-[#49454F]/50 flex flex-col justify-between min-h-[260px]">
                        <div>
                            <div class="bg-[#D0BCFF] w-16 h-16 rounded-2xl flex items-center justify-center mb-8 shadow-lg group-hover:scale-110 transition-transform">
                                <svg class="w-8 h-8 text-[#381E72]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <h4 class="text-xl font-bold text-[#E6E1E5] mb-3 tracking-tight">Budgets</h4>
                            <p class="text-[#CAC4D0] text-sm leading-relaxed font-medium">Allocate and monitor fuel budget quantities per chargeable account.</p>
                        </div>
                        <div class="mt-8 flex items-center text-[#D0BCFF] font-bold text-[10px] uppercase tracking-[0.2em]">
                            Manage Budgets
                            <svg class="w-4 h-4 ml-2 group-hover:translate-x-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </div>
                    </a>
                    @endif

                    <!-- Fuel Orders Card -->
                    <a href="{{ route('fuel-orders.index') }}" class="group bg-[#2D2930] hover:bg-[#49454F]/50 rounded-[28px] !p-10 transition-all duration-300 shadow-xl border border-[#49454F]/50 flex flex-col justify-between min-h-[260px]">
                        <div>
                            <div class="bg-[#F2B8B5] w-16 h-16 rounded-2xl flex items-center justify-center mb-8 shadow-lg group-hover:scale-110 transition-transform">
                                <svg class="w-8 h-8 text-[#601410]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <h4 class="text-xl font-bold text-[#E6E1E5] mb-3 tracking-tight">Fuel Orders</h4>
                            <p class="text-[#CAC4D0] text-sm leading-relaxed font-medium">Issue and manage fuel replenishment orders for assets.</p>
                        </div>
                        <div class="mt-8 flex items-center text-[#F2B8B5] font-bold text-[10px] uppercase tracking-[0.2em]">
                            {{ in_array(Auth::user()->role, ['data_logger', 'data logger', 'administrator']) ? 'Issue New Order' : 'View Orders' }}
                            <svg class="w-4 h-4 ml-2 group-hover:translate-x-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
