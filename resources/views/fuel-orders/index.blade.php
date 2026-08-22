<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-column flex-md-row justify-content-md-between align-items-md-center gap-3">
            <h2 class="h4 fw-bold text-light mb-0">
                {{ __('Fuel Orders') }}
            </h2>
            <div class="d-flex align-items-center gap-3">
                <form action="{{ route('fuel-orders.index') }}" method="GET" class="d-flex align-items-center gap-2">
                    <select name="chargeable_account_id" class="form-select bg-dark text-light border-secondary border-opacity-50 rounded-pill px-3 py-2 text-sm" style="width: 200px;" onchange="this.form.submit()">
                        <option value="">All Accounts</option>
                        @foreach($chargeableAccounts as $acc)
                            <option value="{{ $acc->id }}" {{ request('chargeable_account_id') == $acc->id ? 'selected' : '' }}>
                                {{ $acc->name }}
                            </option>
                        @endforeach
                    </select>

                    <select name="sub_account_id" class="form-select bg-dark text-light border-secondary border-opacity-50 rounded-pill px-3 py-2 text-sm" style="width: 200px;" onchange="this.form.submit()">
                        <option value="">All Sub-Accounts</option>
                        @foreach($subAccounts as $sub)
                            @if(!request('chargeable_account_id') || $sub->chargeable_account_id == request('chargeable_account_id'))
                                <option value="{{ $sub->id }}" {{ request('sub_account_id') == $sub->id ? 'selected' : '' }}>
                                    {{ $sub->name }}
                                </option>
                            @endif
                        @endforeach
                    </select>

                    <select name="status" class="form-select bg-dark text-light border-secondary border-opacity-50 rounded-pill px-3 py-2 text-sm" style="width: 150px;" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="PEND" {{ request('status') === 'PEND' ? 'selected' : '' }}>Pending</option>
                        <option value="PENDING_WAIVER" {{ request('status') === 'PENDING_WAIVER' ? 'selected' : '' }}>Pending Waiver</option>
                        <option value="DONE" {{ request('status') === 'DONE' ? 'selected' : '' }}>Done</option>
                        <option value="VOID" {{ request('status') === 'VOID' ? 'selected' : '' }}>Void</option>
                    </select>

                    <div class="input-group">
                        <input type="text" name="fleet_no" value="{{ request('fleet_no') }}" placeholder="Search Fleet No / Order ID..." class="form-control bg-dark text-light border-secondary border-opacity-50 rounded-start-pill px-4 py-2 text-sm" style="width: 200px;">
                        <button type="submit" class="btn btn-secondary border-secondary border-opacity-50 rounded-end-pill px-4 py-2">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </button>
                    </div>
                </form>

                @if(in_array(Auth::user()->role, ['data_logger', 'administrator']))
                    <a href="{{ route('fuel-orders.create') }}" class="btn btn-primary text-nowrap d-inline-flex align-items-center">
                        <svg class="me-2" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                        {{ __('Create Order') }}
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="container-xl py-5">
        <div class="card bg-dark border-secondary border-opacity-25 shadow-sm overflow-hidden">
            <div class="card-body p-4">
                @if (session('message'))
                    <div class="alert alert-success bg-success bg-opacity-10 border-success border-opacity-20 text-success d-flex align-items-center mb-4 rounded-3" role="alert">
                        <svg class="me-2" width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <div class="fw-bold small">{{ session('message') }}</div>
                    </div>
                @endif

                <div class="table-responsive rounded-3 border border-secondary border-opacity-25">
                    <table class="table table-dark table-hover align-middle mb-0">
                        <thead>
                            <tr class="bg-secondary bg-opacity-10">
                                <th class="ps-4 py-3 text-secondary text-uppercase small fw-bold tracking-widest">ID</th>
                                <th class="px-4 py-3 text-secondary text-uppercase small fw-bold tracking-widest">Asset</th>
                                <th class="px-4 py-3 text-secondary text-uppercase small fw-bold tracking-widest">Chargeable Account</th>
                                <th class="px-4 py-3 text-secondary text-uppercase small fw-bold tracking-widest">Sub-Account</th>
                                <th class="px-4 py-3 text-secondary text-uppercase small fw-bold tracking-widest">Calculated</th>
                                <th class="px-4 py-3 text-secondary text-uppercase small fw-bold tracking-widest">Say Qty</th>
                                <th class="px-4 py-3 text-secondary text-uppercase small fw-bold tracking-widest text-center">Status</th>
                                <th class="px-4 py-3 text-secondary text-uppercase small fw-bold tracking-widest">Actual Qty</th>
                                <th class="px-4 py-3 text-secondary text-uppercase small fw-bold tracking-widest">Date Created</th>
                                <th class="pe-4 py-3 text-secondary text-uppercase small fw-bold tracking-widest text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($fuelOrders as $order)
                                <tr>
                                    <td class="ps-4 py-3">
                                        <span class="text-primary fw-bold font-monospace small">#{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($order->asset)
                                            <div class="fw-bold text-light small tracking-tight">{{ $order->asset->fleet_no }}</div>
                                            <div class="text-secondary small text-uppercase tracking-widest" style="font-size: 10px;">{{ $order->asset->plate_no ?? 'No Plate' }}</div>
                                        @else
                                            <div class="text-secondary small tracking-tight">Direct Order</div>
                                            @if($order->unbudgeted)
                                                <span class="badge bg-danger bg-opacity-10 text-danger" style="font-size: 8px; padding: 1px 4px;">UNBUDGETED</span>
                                            @endif
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="fw-bold text-light small tracking-tight">
                                            {{ $order->chargeableAccount->name ?? '—' }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="fw-bold text-light small tracking-tight">
                                            {{ $order->subAccount->name ?? '—' }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="text-light font-monospace small">{{ number_format($order->calculated_quantity, 2) }} L</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="text-primary fw-bold font-monospace small">{{ number_format($order->say_quantity, 2) }} L</span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @php
                                            if ($order->is_waiver_pending) {
                                                $statusClass = 'bg-danger text-danger bg-opacity-10 border border-danger border-opacity-20';
                                                $statusLabel = 'PENDING WAIVER';
                                            } else {
                                                $statusClass = match($order->status) {
                                                    'DONE' => 'bg-success text-success bg-opacity-10 border border-success border-opacity-20',
                                                    'VOID' => 'bg-danger text-danger bg-opacity-10 border border-danger border-opacity-20',
                                                    default => 'bg-warning text-warning bg-opacity-10 border border-warning border-opacity-20',
                                                };
                                                $statusLabel = $order->status;
                                            }
                                        @endphp
                                        <span class="badge {{ $statusClass }} rounded-pill text-uppercase tracking-widest fw-bold px-3 py-1" style="font-size: 10px;">
                                            {{ $statusLabel }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="text-info fw-bold font-monospace small">
                                            @if($order->status === 'DONE')
                                                {{ number_format($order->actual_quantity, 2) }} L
                                            @else
                                                -
                                            @endif
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-secondary text-uppercase tracking-widest fw-bold" style="font-size: 10px;">
                                        {{ $order->created_at->format('M d, Y H:i') }}
                                    </td>
                                    <td class="pe-4 py-3 text-end">
                                        <div class="d-flex justify-content-end gap-1">
                                            @if(Auth::user()->role === 'administrator')
                                                <a href="{{ route('fuel-orders.edit', $order) }}" class="btn btn-link text-info p-2 rounded-circle hover-bg-light hover-bg-opacity-10" title="Edit Order">
                                                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                </a>
                                            @endif
                                            @if(in_array(Auth::user()->role, ['fuel_man', 'data_logger', 'data logger', 'administrator']) && $order->status === 'PEND' && !$order->is_waiver_pending)
                                                <a href="{{ route('fuel-orders.actualize', $order) }}" class="btn btn-link text-primary p-2 rounded-circle hover-bg-light hover-bg-opacity-10" title="Actualize Quantity">
                                                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                </a>
                                            @endif
                                            <a href="{{ route('fuel-orders.show', $order) }}" class="btn btn-link text-primary p-2 rounded-circle hover-bg-light hover-bg-opacity-10" title="View / Print Order">
                                                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="px-4 py-5 text-center border-0">
                                        <div class="d-flex flex-column align-items-center justify-content-center py-5">
                                            <div class="bg-secondary bg-opacity-20 rounded-4 d-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
                                                <svg width="32" height="32" class="text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                            </div>
                                            <p class="fw-bold text-light mb-1">No fuel orders found.</p>
                                            @if(request('fleet_no') || request('chargeable_account_id') || request('sub_account_id'))
                                                <p class="text-secondary small mb-3">Try adjusting your search filter.</p>
                                                <a href="{{ route('fuel-orders.index') }}" class="btn btn-link text-primary fw-bold text-decoration-none small text-uppercase tracking-widest">Clear Filter</a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($fuelOrders->hasPages())
                    <div class="mt-4">
                        {{ $fuelOrders->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
