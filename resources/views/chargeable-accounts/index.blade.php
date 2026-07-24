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

    <div class="py-5">
<!--
        <div class="container" style="max-width: 900px;">
            <div class="card bg-dark border-secondary shadow-lg rounded-4 overflow-hidden">
                <div class="card-body p-0">

-->
                    <div class="table-responsive ms-3 me-3">
                        <table class="table table-dark table-hover mb-0">
                            <thead>
                                <tr class="bg-secondary bg-opacity-10">
                                    <th class="px-4 py-3 text-uppercase small fw-bold text-secondary tracking-wider">Account</th>
                                    <th class="px-4 py-3 text-uppercase small fw-bold text-secondary tracking-wider">Type</th>
                                    <th class="px-4 py-3 text-uppercase small fw-bold text-secondary tracking-wider">Sub-Account</th>
                                    <th class="px-4 py-3 text-uppercase small fw-bold text-secondary tracking-wider">Status</th>
                                    <th class="px-4 py-3 text-uppercase small fw-bold text-secondary tracking-wider text-end" style="width: 200px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($chargeableAccounts as $account)
                                    <tr>
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

            <!--

                </div>
            </div>
-->
            
            @if($chargeableAccounts->isEmpty())
                <div class="mt-4 text-center card bg-dark border-secondary p-5 rounded-4">
                    <svg class="mx-auto mb-3 text-secondary" width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <h3 class="h5 text-white">No accounts found</h3>
                    <p class="text-secondary">Get started by creating a new chargeable account.</p>
                </div>
            @endif
        <!-- </div> -->
    </div>
</x-app-layout>
