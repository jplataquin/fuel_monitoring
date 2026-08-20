<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div class="d-flex flex-column flex-md-row align-items-md-center gap-3 flex-grow-1" x-data>
                <h2 class="h4 fw-bold text-light mb-0">
                    {{ __('Fleet Inventory') }}
                </h2>
                <div class="ms-md-4 position-relative" style="max-width: 400px; width: 100%;">
                    <span class="position-absolute start-0 top-50 translate-middle-y ms-3 text-secondary">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </span>
                    <input type="text" 
                           @input="$dispatch('search-fleet', $el.value)"
                           placeholder="Search fleet no..." 
                           class="form-control bg-dark border-secondary border-opacity-50 text-light rounded-pill ps-5 py-2 shadow-sm focus-ring-primary"
                           style="font-size: 0.9rem;">
                </div>
            </div>
            @if(in_array(Auth::user()->role, ['administrator', 'moderator']))
                <a href="{{ route('assets.create') }}" class="btn btn-primary d-inline-flex align-items-center">
                    <svg class="me-2" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    {{ __('New Asset') }}
                </a>
            @endif
        </div>
    </x-slot>

    <div class="container-xl py-5" x-data="{ 
        search: '',
        activeTab: {{ $classifications->first() ? $classifications->first()->id : 'null' }},
        assets: {{ $assets->toJson() }},
        get filteredAssets() {
            let filtered = this.assets;
            if (this.activeTab !== null) {
                filtered = filtered.filter(asset => asset.asset_type_id === this.activeTab);
            }
            if (this.search !== '') {
                let term = this.search.toLowerCase();
                filtered = filtered.filter(asset => 
                    asset.fleet_no.toLowerCase().includes(term)
                );
            }
            return filtered;
        }
    }" @search-fleet.window="search = $event.detail">
        <!-- Navigation Tabs -->
        <ul class="nav nav-tabs border-secondary border-opacity-25 mb-4" role="tablist">
            @foreach($classifications as $type)
                @php
                    $count = $assets->where('asset_type_id', $type->id)->count();
                @endphp
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold"
                            :class="activeTab === {{ $type->id }} ? 'active text-primary' : 'text-secondary border-transparent'"
                            @click="activeTab = {{ $type->id }}"
                            type="button" 
                            role="tab">
                        {{ $type->name }} ({{ $count }})
                    </button>
                </li>
            @endforeach
        </ul>

        <!-- Action Cards Grid -->
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <template x-for="asset in filteredAssets" :key="asset.id">
                <div class="col">
                    <!-- Individual Asset Card -->
                    <a :href="'{{ route('assets.index') }}/' + asset.id" class="card h-100 bg-dark border-secondary border-opacity-25 shadow-sm text-decoration-none p-4 transition-all">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h4 class="h5 fw-bold text-light mb-1" x-text="asset.fleet_no"></h4>
                                <p class="small fw-bold text-primary text-uppercase tracking-widest mb-0" style="font-size: 0.7rem;" x-text="asset.asset_type.name"></p>
                            </div>
                            <span class="badge bg-secondary bg-opacity-10 border border-secondary border-opacity-25 text-secondary fw-bold text-uppercase tracking-widest px-2 py-1" style="font-size: 0.6rem;" x-text="asset.plate_no || '—'"></span>
                        </div>
                    </a>
                </div>
            </template>
        </div>

        <div class="card bg-dark border-secondary border-opacity-25 shadow-sm mt-4" x-show="filteredAssets.length === 0">
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
    </div>
</x-app-layout>
