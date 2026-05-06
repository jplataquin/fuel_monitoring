<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <h2 class="h4 fw-bold text-light mb-0">
                {{ __('Fleet Inventory') }}
            </h2>
            @if(in_array(Auth::user()->role, ['administrator', 'moderator']))
                <a href="{{ route('assets.create') }}" class="btn btn-primary d-inline-flex align-items-center">
                    <svg class="me-2" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    {{ __('New Asset') }}
                </a>
            @endif
        </div>
    </x-slot>

    <div class="container-xl py-5">
        <!-- Action Cards Grid -->
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            @foreach($assets as $asset)
                <div class="col">
                    <!-- Individual Asset Card -->
                    <a href="{{ route('assets.show', $asset) }}" class="card h-100 bg-dark border-secondary border-opacity-25 shadow-sm text-decoration-none p-4 transition-all">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h4 class="h5 fw-bold text-light mb-1">{{ $asset->fleet_no }}</h4>
                                <p class="small fw-bold text-primary text-uppercase tracking-widest mb-0" style="font-size: 0.7rem;">{{ $asset->assetType->name }}</p>
                            </div>
                            <span class="badge bg-secondary bg-opacity-10 border border-secondary border-opacity-25 text-secondary fw-bold text-uppercase tracking-widest px-2 py-1" style="font-size: 0.6rem;">
                                {{ $asset->plate_no ?? '—' }}
                            </span>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>

        @if($assets->isEmpty())
            <div class="card bg-dark border-secondary border-opacity-25 shadow-sm mt-4">
                <div class="card-body p-5 text-center">
                    <div class="bg-secondary bg-opacity-10 d-inline-flex align-items-center justify-content-center rounded-circle mb-4" style="width: 80px; height: 80px;">
                        <svg width="40" height="40" class="text-secondary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="h5 fw-bold text-light mb-2">No assets registered</h3>
                    <p class="text-secondary mx-auto" style="max-width: 320px;">Start building your fleet inventory to begin monitoring utilization.</p>
                    @if(in_array(Auth::user()->role, ['administrator', 'moderator']))
                        <div class="mt-4">
                            <a href="{{ route('assets.create') }}" class="btn btn-link text-primary fw-bold text-uppercase tracking-widest text-decoration-none small">
                                Add your first asset
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
