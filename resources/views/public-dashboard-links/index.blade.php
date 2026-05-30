<x-app-layout>
    <x-slot name="header">
        <h2 class="h2 fw-bold text-light mb-0">
            {{ __('Shared Dashboard Links') }}
        </h2>
    </x-slot>

    <div class="py-5">
        <div class="container-xl">
            <div class="vstack gap-4">
                
                <!-- Generate New Link Form -->
                <div class="card bg-dark border-secondary shadow-lg rounded-4 overflow-hidden">
                    <div class="card-body p-4 p-md-5">
                        <h3 class="h5 fw-bold text-light mb-4 d-flex align-items-center">
                            <span class="bg-primary p-2 rounded-3 me-3 text-white">
                                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" /></svg>
                            </span>
                            Generate New Public Link
                        </h3>
                        <form action="{{ route('public-dashboard-links.store') }}" method="POST" class="row g-3 align-items-end">
                            @csrf
                            <div class="col-md-9">
                                <label for="name" class="form-label small fw-bold text-secondary text-uppercase tracking-wider">Link Description (Optional)</label>
                                <input type="text" name="name" id="name" class="form-control bg-dark text-light border-secondary" placeholder="e.g. Board Members, External Audit, etc.">
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold text-uppercase small shadow-sm py-2">
                                    Generate Link
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Existing Links Table -->
                <div class="card bg-dark border-secondary shadow-lg rounded-4 overflow-hidden">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover mb-0 align-middle">
                            <thead>
                                <tr class="bg-secondary bg-opacity-5">
                                    <th class="ps-4 py-3 text-secondary text-uppercase small fw-bold tracking-widest">Description</th>
                                    <th class="py-3 text-secondary text-uppercase small fw-bold tracking-widest">Public URL</th>
                                    <th class="py-3 text-secondary text-uppercase small fw-bold tracking-widest text-center">Status</th>
                                    <th class="py-3 text-secondary text-uppercase small fw-bold tracking-widest">Created By</th>
                                    <th class="pe-4 py-3 text-secondary text-uppercase small fw-bold tracking-widest text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($links as $link)
                                    <tr>
                                        <td class="ps-4 py-3">
                                            <span class="fw-bold text-light small">{{ $link->name ?? 'Unnamed Link' }}</span>
                                            <div class="text-secondary" style="font-size: 0.65rem;">Created {{ $link->created_at->format('M d, Y') }}</div>
                                        </td>
                                        <td class="py-3">
                                            <div class="input-group input-group-sm" style="max-width: 400px;">
                                                <input type="text" class="form-control bg-dark bg-opacity-50 text-secondary border-secondary border-opacity-25 font-monospace" value="{{ route('public.dashboard', $link->slug) }}" readonly id="url-{{ $link->id }}">
                                                <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('{{ $link->id }}')">
                                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" /></svg>
                                                </button>
                                            </div>
                                        </td>
                                        <td class="py-3 text-center">
                                            @if($link->is_active)
                                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 text-uppercase fw-bold tracking-widest" style="font-size: 0.6rem;">Active</span>
                                            @else
                                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 text-uppercase fw-bold tracking-widest" style="font-size: 0.6rem;">Invalidated</span>
                                            @endif
                                        </td>
                                        <td class="py-3 small text-secondary">
                                            {{ $link->creator->name }}
                                        </td>
                                        <td class="pe-4 py-3 text-end">
                                            <div class="d-flex justify-content-end gap-2">
                                                <form action="{{ route('public-dashboard-links.toggle', $link) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-sm {{ $link->is_active ? 'btn-outline-warning' : 'btn-outline-success' }} rounded-pill px-3 fw-bold text-uppercase tracking-widest" style="font-size: 0.6rem;">
                                                        {{ $link->is_active ? 'Invalidate' : 'Activate' }}
                                                    </button>
                                                </form>
                                                <form action="{{ route('public-dashboard-links.destroy', $link) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this link forever?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle p-1">
                                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h14" /></svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-5 text-center text-secondary fw-bold text-uppercase small tracking-widest">
                                            No shared links generated yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function copyToClipboard(id) {
            const copyText = document.getElementById("url-" + id);
            copyText.select();
            copyText.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(copyText.value);
            
            // Visual feedback could be added here
            alert("Copied to clipboard: " + copyText.value);
        }
    </script>
</x-app-layout>
