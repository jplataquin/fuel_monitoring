<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-bold text-light mb-0">
                {{ __('Fuel Order #') }}{{ str_pad($fuelOrder->id, 5, '0', STR_PAD_LEFT) }}
            </h2>
            <a href="{{ route('fuel-orders.index') }}" class="btn btn-secondary rounded-pill px-4 d-inline-flex align-items-center fw-bold small text-uppercase tracking-widest shadow-sm">
                <svg width="16" height="16" class="me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Back to Orders
            </a>
        </div>
    </x-slot>

    <div class="container-xl py-5">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8">
                <div class="card bg-dark border-secondary border-opacity-25 shadow-lg rounded-4 overflow-hidden">
                    <div class="card-body p-4 p-md-5">
                        <form method="POST" action="{{ route('fuel-orders.store-actualization', $fuelOrder) }}">
                            @csrf
                            
                            <div class="text-center mb-5 pb-5 border-bottom border-secondary border-opacity-25">
                                <h1 class="h3 fw-black text-light tracking-tight text-uppercase mb-2">Actualize Fuel Order</h1>
                                <p class="text-secondary mb-4">Order Number: #{{ str_pad($fuelOrder->id, 5, '0', STR_PAD_LEFT) }}</p>
                                
                                <div class="d-inline-flex align-items-center gap-3 p-3 bg-secondary bg-opacity-10 rounded-3 border border-secondary border-opacity-25 shadow-sm">
                                    <label class="text-secondary fw-bold small text-uppercase tracking-wider mb-0">Status:</label>
                                    <div class="bg-dark text-primary border border-primary border-opacity-50 fw-black px-4 py-2 rounded-3 opacity-75">
                                        {{ $fuelOrder->status }}
                                    </div>
                                </div>
                            </div>

                            <div class="row g-4 mb-5">
                                <div class="col-md-6">
                                    <h4 class="small fw-bold text-secondary text-uppercase tracking-wider mb-1">Asset Details</h4>
                                    <p class="h5 fw-bold text-light mb-0">{{ $fuelOrder->asset->fleet_no }}</p>
                                    <p class="small text-secondary mb-0">{{ $fuelOrder->asset->assetType->name ?? 'N/A' }} | {{ $fuelOrder->asset->plate_no ?? 'No Plate' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <h4 class="small fw-bold text-secondary text-uppercase tracking-wider mb-1">Date Range</h4>
                                    <p class="h5 fw-bold text-light mb-0">
                                        {{ \Carbon\Carbon::parse($fuelOrder->date_from)->format('M d, Y') }} 
                                        - 
                                        {{ \Carbon\Carbon::parse($fuelOrder->date_to)->format('M d, Y') }}
                                    </p>
                                </div>
                            </div>

                            <div class="row g-4 mb-5">
                                <div class="col-md-6">
                                    <h4 class="small fw-bold text-secondary text-uppercase tracking-wider mb-1">Calculation Method</h4>
                                    <p class="h6 fw-bold text-light text-capitalize mb-0">{{ $fuelOrder->utilizationEntries->first()?->calculation_type ?? 'N/A' }}</p>
                                </div>
                                <div class="col-md-3">
                                    <h4 class="small fw-bold text-secondary text-uppercase tracking-wider mb-1">KM Factor</h4>
                                    <p class="h6 fw-bold text-primary mb-0">{{ number_format($fuelOrder->fuel_factor_km, 2) }} KM/L</p>
                                </div>
                                <div class="col-md-3">
                                    <h4 class="small fw-bold text-secondary text-uppercase tracking-wider mb-1">HR Factor</h4>
                                    <p class="h6 fw-bold text-primary mb-0">{{ number_format($fuelOrder->fuel_factor_hr, 2) }} L/HR</p>
                                </div>
                            </div>

                            <div class="row g-3 mb-5">
                                <div class="col-md-4">
                                    <div class="bg-secondary bg-opacity-10 rounded-3 p-3 border border-secondary border-opacity-10 text-center shadow-sm h-100 d-flex flex-column justify-content-center">
                                        <h4 class="small fw-bold text-secondary text-uppercase tracking-wider mb-2">Total KM</h4>
                                        <p class="h4 fw-black text-light mb-0 font-monospace">{{ number_format($fuelOrder->calculated_kilometers, 2) }}</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="bg-secondary bg-opacity-10 rounded-3 p-3 border border-secondary border-opacity-10 text-center shadow-sm h-100 d-flex flex-column justify-content-center">
                                        <h4 class="small fw-bold text-secondary text-uppercase tracking-wider mb-2">Total Hours</h4>
                                        <p class="h4 fw-black text-light mb-0 font-monospace">{{ number_format($fuelOrder->calculated_hours, 2) }}</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="bg-primary bg-opacity-10 rounded-3 p-3 border border-primary border-opacity-10 text-center shadow-sm h-100 d-flex flex-column justify-content-center">
                                        <h4 class="small fw-bold text-primary text-uppercase tracking-wider mb-2">Calculated (L)</h4>
                                        <p class="h4 fw-black text-primary mb-0 font-monospace">{{ number_format($fuelOrder->calculated_quantity, 2) }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-secondary bg-opacity-10 rounded-4 p-4 p-md-5 mb-5 shadow-inner border border-secondary border-opacity-25">
                                <div class="mb-4">
                                    <label for="say_quantity" class="form-label h6 fw-bold text-secondary text-uppercase tracking-wider mb-2">Say Fuel Quantity (Approved)</label>
                                    <div class="input-group input-group-lg shadow-sm">
                                        <input type="number" step="0.01" name="say_quantity" id="say_quantity" value="{{ old('say_quantity', $fuelOrder->say_quantity) }}" class="form-control bg-dark text-primary border-secondary border-opacity-50 fw-black px-4 font-monospace opacity-75" readonly placeholder="0.00">
                                        <span class="input-group-text bg-dark text-primary border-secondary border-opacity-50 fw-black px-4">L</span>
                                    </div>
                                </div>

                                <div class="border-top border-secondary border-opacity-25 pt-4">
                                    <label for="actual_quantity" class="form-label h6 fw-bold text-secondary text-uppercase tracking-wider mb-2">Actual Quantity Dispensed (Liters) <span class="text-danger">*</span></label>
                                    <div class="input-group input-group-lg shadow-sm">
                                        <input type="number" step="0.01" name="actual_quantity" id="actual_quantity" value="{{ old('actual_quantity', $fuelOrder->actual_quantity) }}" class="form-control bg-dark text-light border-secondary border-opacity-50 fw-bold px-4 font-monospace" required placeholder="0.00">
                                        <span class="input-group-text bg-dark text-secondary border-secondary border-opacity-50 fw-bold px-4">L</span>
                                    </div>
                                    @error('actual_quantity')
                                        <p class="text-danger small fw-bold mt-2">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-3 pt-4 border-top border-secondary border-opacity-25">
                                <a href="{{ route('fuel-orders.index') }}" class="btn btn-secondary rounded-pill px-5 py-2 fw-black small text-uppercase tracking-widest shadow-sm">
                                    Cancel
                                </a>
                                <button type="submit" class="btn btn-primary rounded-pill px-5 py-2 fw-black small text-uppercase tracking-widest shadow-lg hover-translate-y">
                                    <svg width="20" height="20" class="me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    Actualize
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
