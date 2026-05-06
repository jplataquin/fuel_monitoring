<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <h2 class="h4 font-weight-bold text-light mb-0">
                {{ __('User Management') }}
            </h2>
            <div class="d-flex flex-wrap gap-2">
                @if(Auth::user()->role === 'administrator')
                    <a href="{{ route('users.create-moderator') }}" class="btn btn-primary rounded-pill shadow-sm px-4 fw-bold small text-uppercase">
                        {{ __('Add Moderator') }}
                    </a>
                @endif
                <a href="{{ route('users.create-data-logger') }}" class="btn btn-info rounded-pill shadow-sm px-4 fw-bold small text-uppercase text-dark">
                    {{ __('Add Data Logger') }}
                </a>
                <a href="{{ route('users.create-fuel-man') }}" class="btn btn-secondary rounded-pill shadow-sm px-4 fw-bold small text-uppercase" style="background-color: #bb86fc; border-color: #bb86fc; color: #121212;">
                    {{ __('Add Fuel Man') }}
                </a>
                <a href="{{ route('users.create-budgeteer') }}" class="btn btn-success rounded-pill shadow-sm px-4 fw-bold small text-uppercase">
                    {{ __('Add Budgeteer') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-5">
        <div class="container" style="max-width: 1024px;">
            <div class="card bg-dark border-secondary shadow-lg rounded-4 overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-dark table-hover mb-0 align-middle border-secondary">
                        <thead class="table-secondary">
                            <tr class="text-uppercase small fw-bold tracking-widest">
                                <th class="px-4 py-3 border-secondary">Name</th>
                                <th class="px-4 py-3 border-secondary">Email Address</th>
                                <th class="px-4 py-3 border-secondary">Role</th>
                                <th class="px-4 py-3 border-secondary text-end" style="width: 150px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="border-secondary">
                            @foreach($users as $user)
                                <tr class="border-secondary">
                                    <td class="px-4 py-4 border-secondary">
                                        <span class="h6 fw-bold text-light mb-0">{{ $user->name }}</span>
                                    </td>
                                    <td class="px-4 py-4 border-secondary">
                                        <span class="text-secondary font-monospace small">{{ $user->email }}</span>
                                    </td>
                                    <td class="px-4 py-4 border-secondary">
                                        @php
                                            $badgeClass = 'bg-success bg-opacity-10 text-success border-success';
                                            if ($user->role === 'administrator') $badgeClass = 'bg-warning bg-opacity-10 text-warning border-warning';
                                            elseif ($user->role === 'moderator') $badgeClass = 'bg-primary bg-opacity-10 text-primary border-primary';
                                            elseif ($user->role === 'budgeteer') $badgeClass = 'bg-danger bg-opacity-10 text-danger border-danger';
                                            elseif ($user->role === 'fuel_man') $badgeClass = 'bg-info bg-opacity-10 text-info border-info';
                                        @endphp
                                        <span class="badge {{ $badgeClass }} border opacity-75 rounded-pill text-uppercase tracking-widest px-3 py-2" style="font-size: 0.65rem;">
                                            {{ str_replace('_', ' ', $user->role) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 border-secondary text-end">
                                        <div class="d-flex justify-content-end gap-1">
                                            <a href="{{ route('users.edit', $user) }}" class="btn btn-outline-primary btn-sm rounded-circle p-2 d-inline-flex align-items-center justify-content-center shadow-none" style="width: 38px; height: 38px;" title="Edit / Reset Password">
                                                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                            </a>
                                            <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm rounded-circle p-2 d-inline-flex align-items-center justify-content-center shadow-none" style="width: 38px; height: 38px;" onclick="return confirm('Are you sure you want to soft delete this user?')" title="Delete User">
                                                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
