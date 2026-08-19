<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 font-weight-bold text-white mb-0">
                {{ __('Chargeable Accounts') }}
            </h2>
            @if(in_array(Auth::user()->role, ['administrator', 'moderator', 'budgeteer']))
                <a href="{{ route('chargeable-accounts.create') }}" class="btn btn-primary d-flex align-items-center rounded-pill px-4">
                    <svg class="me-2" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    {{ __('Add Account') }}
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-5" x-data="{
        search: '',
        showInactive: false,
        sortBy: 'account',
        sortAsc: true,
        accounts: {{ $chargeableAccounts->map(fn($a) => ['id' => $a->id, 'name' => strtolower($a->name), 'status' => $a->status])->toJson() }},
        get hasVisibleAccounts() {
            return this.accounts.some(a => 
                (this.search === '' || a.name.includes(this.search.toLowerCase())) &&
                (this.showInactive || a.status === 'Active')
            );
        },
        sort(column) {
            if (this.sortBy === column) {
                this.sortAsc = !this.sortAsc;
            } else {
                this.sortBy = column;
                this.sortAsc = true;
            }
            this.sortTable();
        },
        sortTable() {
            let table = this.$refs.accountsTable;
            let rows = Array.from(table.querySelectorAll('tbody tr'));
            
            rows.sort((a, b) => {
                let valA = a.getAttribute('data-' + this.sortBy) || '';
                let valB = b.getAttribute('data-' + this.sortBy) || '';
                
                if (this.sortBy === 'sub-account') {
                    return (parseInt(valA) - parseInt(valB)) * (this.sortAsc ? 1 : -1);
                }
                if (this.sortBy === 'approved-budget' || this.sortBy === 'pending-budget') {
                    return (parseFloat(valA) - parseFloat(valB)) * (this.sortAsc ? 1 : -1);
                }
                
                return valA.localeCompare(valB, undefined, {numeric: true, sensitivity: 'base'}) * (this.sortAsc ? 1 : -1);
            });
            
            let tbody = table.querySelector('tbody');
            rows.forEach(row => tbody.appendChild(row));
        }
    }">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 ms-3 me-3">
            <div class="position-relative" style="max-width: 400px; width: 100%;">
                <span class="position-absolute start-0 top-50 translate-middle-y ms-3 text-secondary">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input type="text" 
                       x-model="search"
                       placeholder="Search accounts..." 
                       class="form-control bg-dark border-secondary border-opacity-50 text-light rounded-pill ps-5 py-2 shadow-sm focus-ring-primary"
                       style="font-size: 0.9rem;">
            </div>
            
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" role="switch" id="showInactiveToggle" x-model="showInactive">
                <label class="form-check-label text-secondary small fw-bold text-uppercase" style="cursor: pointer;" for="showInactiveToggle">Show Inactive Accounts</label>
            </div>
        </div>
        
            <div class="table-responsive ms-3 me-3" x-show="hasVisibleAccounts">
                <table class="table table-dark table-hover mb-0" x-ref="accountsTable">
                    <thead>
                        <tr class="bg-secondary bg-opacity-10">
                            <th class="px-4 py-3 text-uppercase small fw-bold text-secondary tracking-wider" style="cursor: pointer; user-select: none;" @click="sort('account')">
                                Account
                                <span class="ms-1" x-show="sortBy === 'account'">
                                    <i :class="sortAsc ? 'bi bi-arrow-up-short' : 'bi bi-arrow-down-short'"></i>
                                </span>
                                <span class="ms-1 opacity-25" x-show="sortBy !== 'account'">
                                    <i class="bi bi-arrow-down-up" style="font-size: 0.8rem;"></i>
                                </span>
                            </th>
                            <th class="px-4 py-3 text-uppercase small fw-bold text-secondary tracking-wider" style="cursor: pointer; user-select: none;" @click="sort('type')">
                                Type
                                <span class="ms-1" x-show="sortBy === 'type'">
                                    <i :class="sortAsc ? 'bi bi-arrow-up-short' : 'bi bi-arrow-down-short'"></i>
                                </span>
                                <span class="ms-1 opacity-25" x-show="sortBy !== 'type'">
                                    <i class="bi bi-arrow-down-up" style="font-size: 0.8rem;"></i>
                                </span>
                            </th>
                            <th class="px-4 py-3 text-uppercase small fw-bold text-secondary tracking-wider" style="cursor: pointer; user-select: none;" @click="sort('sub-account')">
                                Sub-Account
                                <span class="ms-1" x-show="sortBy === 'sub-account'">
                                    <i :class="sortAsc ? 'bi bi-arrow-up-short' : 'bi bi-arrow-down-short'"></i>
                                </span>
                                <span class="ms-1 opacity-25" x-show="sortBy !== 'sub-account'">
                                    <i class="bi bi-arrow-down-up" style="font-size: 0.8rem;"></i>
                                </span>
                            </th>
                            <th class="px-4 py-3 text-uppercase small fw-bold text-secondary tracking-wider" style="cursor: pointer; user-select: none;" @click="sort('approved-budget')">
                                Total Approved Budget
                                <span class="ms-1" x-show="sortBy === 'approved-budget'">
                                    <i :class="sortAsc ? 'bi bi-arrow-up-short' : 'bi bi-arrow-down-short'"></i>
                                </span>
                                <span class="ms-1 opacity-25" x-show="sortBy !== 'approved-budget'">
                                    <i class="bi bi-arrow-down-up" style="font-size: 0.8rem;"></i>
                                </span>
                            </th>
                            <th class="px-4 py-3 text-uppercase small fw-bold text-secondary tracking-wider" style="cursor: pointer; user-select: none;" @click="sort('pending-budget')">
                                Total Pending Budget
                                <span class="ms-1" x-show="sortBy === 'pending-budget'">
                                    <i :class="sortAsc ? 'bi bi-arrow-up-short' : 'bi bi-arrow-down-short'"></i>
                                </span>
                                <span class="ms-1 opacity-25" x-show="sortBy !== 'pending-budget'">
                                    <i class="bi bi-arrow-down-up" style="font-size: 0.8rem;"></i>
                                </span>
                            </th>
                            <th class="px-4 py-3 text-uppercase small fw-bold text-secondary tracking-wider" style="cursor: pointer; user-select: none;" @click="sort('status')">
                                Status
                                <span class="ms-1" x-show="sortBy === 'status'">
                                    <i :class="sortAsc ? 'bi bi-arrow-up-short' : 'bi bi-arrow-down-short'"></i>
                                </span>
                                <span class="ms-1 opacity-25" x-show="sortBy !== 'status'">
                                    <i class="bi bi-arrow-down-up" style="font-size: 0.8rem;"></i>
                                </span>
                            </th>
                            <th class="px-4 py-3 text-uppercase small fw-bold text-secondary tracking-wider text-end" style="width: 200px; user-select: none;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($chargeableAccounts as $account)
                            <tr x-show="(search === '' || '{{ addslashes(strtolower($account->name)) }}'.includes(search.toLowerCase())) && (showInactive || '{{ $account->status }}' === 'Active')"
                                data-account="{{ strtolower($account->name) }}"
                                data-type="{{ strtolower($account->classification) }}"
                                data-sub-account="{{ $account->sub_accounts_count }}"
                                data-approved-budget="{{ $account->total_approved_budget ?? 0 }}"
                                data-pending-budget="{{ $account->total_pending_budget ?? 0 }}"
                                data-status="{{ strtolower($account->status) }}">
                                <td class="px-4 py-3 align-middle">
                                    <span class="fw-bold text-light">{{ $account->name }}</span>
                                </td>
                                <td class="px-4 py-3 align-middle">
                                    @if($account->classification === 'Scoped')
                                        <span class="text-info small fw-bold">{{ $account->classification }}</span>
                                        <div class="text-secondary small" style="font-size: 0.7rem;">
                                            {{ $account->start_date?->format('M d, Y') }} - {{ $account->end_date?->format('M d, Y') }}
                                        </div>
                                    @else
                                        <span class="text-secondary small fw-bold">{{ $account->classification }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 align-middle">
                                    <span class="text-light small fw-bold">{{ $account->sub_accounts_count }}</span>
                                </td>
                                <td class="px-4 py-3 align-middle">
                                    <span class="text-success small fw-bold font-monospace">{{ number_format($account->total_approved_budget ?? 0, 2) }} L</span>
                                </td>
                                <td class="px-4 py-3 align-middle">
                                    <span class="text-warning small fw-bold font-monospace">{{ number_format($account->total_pending_budget ?? 0, 2) }} L</span>
                                </td>
                                <td class="px-4 py-3 align-middle">
                                    <span class="badge rounded-pill {{ $account->status === 'Active' ? 'bg-success' : 'bg-danger' }}">
                                        {{ $account->status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 align-middle text-end">
                                    <div class="btn-group">
                                        <a href="{{ route('chargeable-accounts.show', $account) }}" class="btn btn-link text-info p-2 rounded-circle" title="View Details">
                                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                        </a>
                                        @if(in_array(Auth::user()->role, ['administrator', 'moderator', 'budgeteer']))
                                            <a href="{{ route('chargeable-accounts.edit', $account) }}" class="btn btn-link text-warning p-2 rounded-circle" title="Edit">
                                                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                            </a>
                                            <form action="{{ route('chargeable-accounts.destroy', $account) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-link text-danger p-2 rounded-circle" onclick="return confirm('Are you sure?')" title="Delete">
                                                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4 text-center card bg-dark border-secondary p-5 rounded-4 ms-3 me-3" x-show="!hasVisibleAccounts && {{ $chargeableAccounts->isNotEmpty() ? 'true' : 'false' }}">
                <svg class="mx-auto mb-3 text-secondary" width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <h3 class="h5 text-white">No matching accounts found</h3>
                <p class="text-secondary">Try adjusting your search terms or enabling "Show Inactive Accounts".</p>
            </div>

            @if($chargeableAccounts->isEmpty())
                <div class="mt-4 text-center card bg-dark border-secondary p-5 rounded-4 ms-3 me-3">
                    <svg class="mx-auto mb-3 text-secondary" width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <h3 class="h5 text-white">No accounts found</h3>
                    <p class="text-secondary">Get started by creating a new chargeable account.</p>
                </div>
            @endif
    </div>
</x-app-layout>
