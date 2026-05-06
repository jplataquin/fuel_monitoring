<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-start gap-4">
            <div>
                <nav aria-label="breadcrumb" class="mb-3">
                    <ol class="breadcrumb mb-0 text-uppercase small fw-bold tracking-widest">
                        <li class="breadcrumb-item"><a href="{{ route('assets.index') }}" class="text-secondary text-decoration-none hover-text-primary">Fleet</a></li>
                        <li class="breadcrumb-item active text-primary" aria-current="page">{{ $asset->fleet_no }}</li>
                    </ol>
                </nav>
                <h2 class="h2 fw-bold text-light mb-0">
                    {{ $asset->fleet_no }}
                </h2>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @if(in_array(Auth::user()->role, ['administrator', 'moderator']))
                    <a href="{{ route('assets.edit', $asset) }}" class="btn btn-outline-secondary rounded-pill d-inline-flex align-items-center px-4 py-2">
                        <svg class="me-2" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                        {{ __('Edit Asset') }}
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="container-xl py-5">
        <div class="row g-4 mb-5">
            <!-- Asset Specs -->
            <div class="col-lg-8">
                <div class="card h-100 bg-dark border-secondary border-opacity-25 shadow-sm">
                    <div class="card-body p-4 p-md-5">
                        <h3 class="text-secondary text-uppercase small fw-bold tracking-widest mb-4 d-flex align-items-center">
                            <span class="bg-primary bg-opacity-25 p-1 rounded-circle me-3" style="width: 24px; height: 4px;"></span>
                            Technical Specifications
                        </h3>
                        <div class="row g-4 row-cols-1 row-cols-sm-2">
                            <div class="col d-flex align-items-start gap-3">
                                <div class="bg-primary bg-opacity-10 p-2 rounded-3 text-primary">
                                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                                </div>
                                <div>
                                    <p class="text-secondary text-uppercase small fw-bold tracking-widest mb-1" style="font-size: 0.65rem;">Equipment Type</p>
                                    <p class="h6 fw-bold text-light mb-0">{{ $asset->assetType->name }}</p>
                                </div>
                            </div>
                            <div class="col d-flex align-items-start gap-3">
                                <div class="bg-primary bg-opacity-10 p-2 rounded-3 text-primary">
                                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" /></svg>
                                </div>
                                <div>
                                    <p class="text-secondary text-uppercase small fw-bold tracking-widest mb-1" style="font-size: 0.65rem;">Plate Number</p>
                                    <p class="h6 fw-bold text-light font-monospace mb-0">{{ $asset->plate_no ?? 'UNASSIGNED' }}</p>
                                </div>
                            </div>
                            <div class="col d-flex align-items-start gap-3">
                                <div class="bg-primary bg-opacity-10 p-2 rounded-3 text-primary">
                                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
                                </div>
                                <div>
                                    <p class="text-secondary text-uppercase small fw-bold tracking-widest mb-1" style="font-size: 0.65rem;">Tank Capacity</p>
                                    <p class="h6 fw-bold text-light mb-0">{{ number_format($asset->tank_capacity, 2) }} <span class="small text-secondary fw-medium ms-1">LITERS</span></p>
                                </div>
                            </div>
                            <div class="col d-flex align-items-start gap-3">
                                <div class="bg-primary bg-opacity-10 p-2 rounded-3 text-primary">
                                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
                                </div>
                                <div>
                                    <p class="text-secondary text-uppercase small fw-bold tracking-widest mb-1" style="font-size: 0.65rem;">Fuel Type</p>
                                    <p class="h6 fw-bold text-light text-uppercase mb-0">{{ $asset->fuel_type ?? 'Diesel' }}</p>
                                </div>
                            </div>
                            <div class="col d-flex align-items-start gap-3">
                                <div class="bg-primary bg-opacity-10 p-2 rounded-3 text-primary">
                                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                                </div>
                                <div>
                                    <p class="text-secondary text-uppercase small fw-bold tracking-widest mb-1" style="font-size: 0.65rem;">Factor (KM)</p>
                                    <p class="h6 fw-bold text-light mb-0">{{ $asset->fuel_factor_km ?? '0.00' }} <span class="small text-secondary fw-medium ms-1">KM/L</span></p>
                                </div>
                            </div>
                            <div class="col d-flex align-items-start gap-3">
                                <div class="bg-primary bg-opacity-10 p-2 rounded-3 text-primary">
                                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </div>
                                <div>
                                    <p class="text-secondary text-uppercase small fw-bold tracking-widest mb-1" style="font-size: 0.65rem;">Factor (HR)</p>
                                    <p class="h6 fw-bold text-light mb-0">{{ $asset->fuel_factor_hr ?? '0.00' }} <span class="small text-secondary fw-medium ms-1">L/HR</span></p>
                                </div>
                            </div>
                            <div class="col d-flex align-items-start gap-3">
                                <div class="bg-primary bg-opacity-10 p-2 rounded-3 text-primary">
                                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A2 2 0 013 15.487V4.513a2 2 0 011.553-1.943L9 1.5l5.447 2.724A2 2 0 0116 6.164v10.973a2 2 0 01-1.553 1.943L9 21.5z" /></svg>
                                </div>
                                <div>
                                    <p class="text-secondary text-uppercase small fw-bold tracking-widest mb-1" style="font-size: 0.65rem;">Last Odometer</p>
                                    <p class="h6 fw-bold text-light mb-0">{{ number_format($asset->last_kilometer_reading, 2) }} <span class="small text-secondary fw-medium ms-1">KM</span></p>
                                </div>
                            </div>
                            <div class="col d-flex align-items-start gap-3">
                                <div class="bg-primary bg-opacity-10 p-2 rounded-3 text-primary">
                                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </div>
                                <div>
                                    <p class="text-secondary text-uppercase small fw-bold tracking-widest mb-1" style="font-size: 0.65rem;">Last Engine Hours</p>
                                    <p class="h6 fw-bold text-light mb-0">{{ number_format($asset->last_engine_hours, 2) }} <span class="small text-secondary fw-medium ms-1">HRS</span></p>
                                </div>
                            </div>
                            <div class="col d-flex align-items-start gap-3">
                                <div class="bg-primary bg-opacity-10 p-2 rounded-3 text-primary">
                                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </div>
                                <div>
                                    <p class="text-secondary text-uppercase small fw-bold tracking-widest mb-1" style="font-size: 0.65rem;">Last Time</p>
                                    <p class="h6 fw-bold text-light mb-0">{{ $asset->last_time ? date('H:i', strtotime($asset->last_time)) : 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="col d-flex align-items-start gap-3">
                                <div class="bg-primary bg-opacity-10 p-2 rounded-3 text-primary">
                                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                </div>
                                <div>
                                    <p class="text-secondary text-uppercase small fw-bold tracking-widest mb-1" style="font-size: 0.65rem;">Last Date</p>
                                    <p class="h6 fw-bold text-light mb-0">{{ $asset->last_date ? date('M d, Y', strtotime($asset->last_date)) : 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card h-100 bg-primary border-0 shadow-lg text-center overflow-hidden position-relative">
                    <div class="card-body p-5 d-flex flex-column justify-content-center position-relative z-1">
                        <p class="text-white text-opacity-75 text-uppercase small fw-bold tracking-widest mb-4">Lifecycle Stats</p>
                        <div class="mb-2">
                            <p class="display-3 fw-bold text-white mb-0" id="total-logs">0</p>
                        </div>
                        <p class="small fw-bold text-white text-opacity-75 text-uppercase tracking-widest">Total Logs</p>
                    </div>
                    <!-- Decorative background element -->
                    <div class="position-absolute bottom-0 end-0 bg-white opacity-10 rounded-circle" style="width: 150px; height: 150px; transform: translate(30%, 30%); filter: blur(40px);"></div>
                </div>
            </div>
        </div>

        <!-- Add Utilization Entry Form -->
        <div class="card bg-dark border-primary border-opacity-25 shadow-lg position-relative overflow-hidden mb-5">
            <div class="position-absolute top-0 end-0 p-4 opacity-10 pointer-events-none">
                <svg width="120" height="120" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
            </div>
            
            <div class="card-body p-4 p-md-5 position-relative z-1">
                <h3 class="h4 fw-bold text-light mb-5 d-flex align-items-center">
                    <span class="bg-primary p-2 rounded-3 me-3 text-white">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    </span>
                    Register Utilization
                </h3>
                
                <form id="utilization-form">
                    @csrf
                    <input type="hidden" name="asset_id" value="{{ $asset->id }}">
                    
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label for="reference" class="form-label text-secondary text-uppercase small fw-bold tracking-widest ps-1 mb-2">Reference</label>
                            <input id="reference" name="reference" type="text" class="form-control bg-dark bg-opacity-25 border-secondary border-opacity-50 text-light focus-ring focus-ring-primary" required placeholder="e.g. REF-001">
                            <p class="text-danger small fw-bold mt-1 ps-1 d-none" id="error-reference"></p>
                        </div>

                        <div class="col-md-6">
                            <label for="driver_operator_name" class="form-label text-secondary text-uppercase small fw-bold tracking-widest ps-1 mb-2">Personnel In-Charge</label>
                            <input id="driver_operator_name" name="driver_operator_name" type="text" class="form-control bg-dark bg-opacity-25 border-secondary border-opacity-50 text-light focus-ring focus-ring-primary" required placeholder="Driver or Operator">
                            <p class="text-danger small fw-bold mt-1 ps-1 d-none" id="error-driver_operator_name"></p>
                        </div>

                        <div class="col-md-4">
                            <label for="date" class="form-label text-secondary text-uppercase small fw-bold tracking-widest ps-1 mb-2">Date</label>
                            <input id="date" name="date" type="date" class="form-control bg-dark bg-opacity-25 border-secondary border-opacity-50 text-light focus-ring focus-ring-primary" value="{{ date('Y-m-d') }}" required>
                            <p class="text-danger small fw-bold mt-1 ps-1 d-none" id="error-date"></p>
                        </div>
                        <div class="col-md-4">
                            <label for="start_time" class="form-label text-secondary text-uppercase small fw-bold tracking-widest ps-1 mb-2">Start Time</label>
                            <input id="start_time" name="start_time" type="time" class="form-control bg-dark bg-opacity-25 border-secondary border-opacity-50 text-light focus-ring focus-ring-primary" value="{{ date('H:i') }}" required>
                            <p class="text-danger small fw-bold mt-1 ps-1 d-none" id="error-start_time"></p>
                        </div>
                        <div class="col-md-4">
                            <label for="end_time" class="form-label text-secondary text-uppercase small fw-bold tracking-widest ps-1 mb-2">End Time</label>
                            <input id="end_time" name="end_time" type="time" class="form-control bg-dark bg-opacity-25 border-secondary border-opacity-50 text-light focus-ring focus-ring-primary" value="{{ date('H:i', strtotime('+1 hour')) }}" required>
                            <p class="text-danger small fw-bold mt-1 ps-1 d-none" id="error-end_time"></p>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="chargeable_account_id" class="form-label text-secondary text-uppercase small fw-bold tracking-widest ps-1 mb-2">Charged To</label>
                            <select id="chargeable_account_id" name="chargeable_account_id" class="form-select bg-dark bg-opacity-25 border-secondary border-opacity-50 text-light focus-ring focus-ring-primary" required onchange="fetchSubAccounts(this.value)">
                                <option value="">-- Select Account --</option>
                                @foreach($chargeableAccounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->name }}</option>
                                @endforeach
                            </select>
                            <p class="text-danger small fw-bold mt-1 ps-1 d-none" id="error-chargeable_account_id"></p>
                        </div>
                        <div class="col-md-6">
                            <label for="sub_account_id" class="form-label text-secondary text-uppercase small fw-bold tracking-widest ps-1 mb-2">Sub Account</label>
                            <select id="sub_account_id" name="sub_account_id" class="form-select bg-dark bg-opacity-25 border-secondary border-opacity-50 text-light focus-ring focus-ring-primary" required disabled>
                                <option value="">-- Select Sub Account --</option>
                            </select>
                            <p class="text-danger small fw-bold mt-1 ps-1 d-none" id="error-sub_account_id"></p>
                        </div>
                        <div class="col-md-6">
                            <label for="calculation_type" class="form-label text-secondary text-uppercase small fw-bold tracking-widest ps-1 mb-2">Calculation Type</label>
                            <select id="calculation_type" name="calculation_type" class="form-select bg-dark bg-opacity-25 border-secondary border-opacity-50 text-light focus-ring focus-ring-primary" required>
                                <option value="">-- Select Calculation Type --</option>
                                <option value="Kilometer Reading">Kilometer Reading</option>
                                <option value="Hour Reading">Hour Reading</option>
                                <option value="Actual Operation Hours">Actual Operation Hours</option>
                            </select>
                            <p class="text-danger small fw-bold mt-1 ps-1 d-none" id="error-calculation_type"></p>
                        </div>
                        <div class="col-md-6 d-flex align-items-end pb-3">
                             <div class="form-check">
                                <input type="checkbox" name="unbudgeted" value="1" id="unbudgeted" class="form-check-input bg-dark border-primary">
                                <label class="form-check-label text-secondary text-uppercase small fw-bold tracking-widest ms-2" for="unbudgeted">
                                    {{ __('Unbudgeted') }}
                                </label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label for="particulars" class="form-label text-secondary text-uppercase small fw-bold tracking-widest ps-1 mb-2">Particulars / Mission</label>
                            <input id="particulars" name="particulars" type="text" class="form-control bg-dark bg-opacity-25 border-secondary border-opacity-50 text-light focus-ring focus-ring-primary" required placeholder="Describe the activity...">
                            <p class="text-danger small fw-bold mt-1 ps-1 d-none" id="error-particulars"></p>
                        </div>
                        <div class="col-md-3">
                            <label for="start_kilometer_reading" class="form-label text-secondary text-uppercase small fw-bold tracking-widest ps-1 mb-2">Start Odo (KM)</label>
                            <input id="start_kilometer_reading" name="start_kilometer_reading" type="number" step="0.01" class="form-control bg-dark bg-opacity-25 border-secondary border-opacity-50 text-light font-monospace focus-ring focus-ring-primary" value="0" onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode == 46">
                            <p class="text-danger small fw-bold mt-1 ps-1 d-none" id="error-start_kilometer_reading"></p>
                        </div>
                        <div class="col-md-3">
                            <label for="end_kilometer_reading" class="form-label text-secondary text-uppercase small fw-bold tracking-widest ps-1 mb-2">End Odo (KM)</label>
                            <input id="end_kilometer_reading" name="end_kilometer_reading" type="number" step="0.01" class="form-control bg-dark bg-opacity-25 border-secondary border-opacity-50 text-light font-monospace focus-ring focus-ring-primary" value="0" onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode == 46">
                            <p class="text-danger small fw-bold mt-1 ps-1 d-none" id="error-end_kilometer_reading"></p>
                        </div>
                        <div class="col-md-3">
                            <label for="start_hour_reading" class="form-label text-secondary text-uppercase small fw-bold tracking-widest ps-1 mb-2">Start Engine (HR)</label>
                            <input id="start_hour_reading" name="start_hour_reading" type="number" step="0.01" class="form-control bg-dark bg-opacity-25 border-secondary border-opacity-50 text-light font-monospace focus-ring focus-ring-primary" value="0" onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode == 46">
                            <p class="text-danger small fw-bold mt-1 ps-1 d-none" id="error-start_hour_reading"></p>
                        </div>
                        <div class="col-md-3">
                            <label for="end_hour_reading" class="form-label text-secondary text-uppercase small fw-bold tracking-widest ps-1 mb-2">End Engine (HR)</label>
                            <input id="end_hour_reading" name="end_hour_reading" type="number" step="0.01" class="form-control bg-dark bg-opacity-25 border-secondary border-opacity-50 text-light font-monospace focus-ring focus-ring-primary" value="0" onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode == 46">
                            <p class="text-danger small fw-bold mt-1 ps-1 d-none" id="error-end_hour_reading"></p>
                        </div>
                        <div class="col-12">
                            <label for="remarks" class="form-label text-secondary text-uppercase small fw-bold tracking-widest ps-1 mb-2">Remarks</label>
                            <textarea id="remarks" name="remarks" rows="2" class="form-control bg-dark bg-opacity-25 border-secondary border-opacity-50 text-light focus-ring focus-ring-primary" placeholder="Any additional notes..."></textarea>
                            <p class="text-danger small fw-bold mt-1 ps-1 d-none" id="error-remarks"></p>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end pt-5 mt-5 border-top border-secondary border-opacity-25">
                        <button type="submit" id="submit-btn" class="btn btn-primary rounded-pill px-5 py-3 fw-bold text-uppercase tracking-widest shadow-sm">
                            <span id="btn-text">Submit Entry</span>
                            <div id="btn-spinner" class="spinner-border spinner-border-sm ms-2 d-none" role="status"></div>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Utilization Logs -->
        <div class="card bg-dark border-secondary border-opacity-25 shadow-sm overflow-hidden">
            <div class="card-header bg-secondary bg-opacity-10 py-4 px-4 border-bottom border-secondary border-opacity-25">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-4">
                    <h3 class="h5 fw-bold text-light mb-0 tracking-tight">{{ __('Utilization Logs') }}</h3>
                    
                    <div class="d-flex flex-wrap align-items-center gap-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-secondary text-uppercase small fw-bold tracking-widest" style="font-size: 0.6rem;">From</span>
                            <input id="filter_start_date" type="date" class="form-control form-control-sm bg-dark border-secondary border-opacity-50 text-light" style="width: 130px;">
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-secondary text-uppercase small fw-bold tracking-widest" style="font-size: 0.6rem;">To</span>
                            <input id="filter_end_date" type="date" class="form-control form-control-sm bg-dark border-secondary border-opacity-50 text-light" style="width: 130px;">
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-secondary text-uppercase small fw-bold tracking-widest" style="font-size: 0.6rem;">Account</span>
                            <select id="filter_chargeable_account_id" class="form-select form-select-sm bg-dark border-secondary border-opacity-50 text-light" style="width: 150px;">
                                <option value="">All</option>
                                @foreach($chargeableAccounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-secondary text-uppercase small fw-bold tracking-widest" style="font-size: 0.6rem;">Order ID</span>
                            <input id="filter_fuel_order_id" type="number" class="form-control form-control-sm bg-dark border-secondary border-opacity-50 text-light" style="width: 80px;" placeholder="ID">
                        </div>
                        <button onclick="applyFilter()" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold text-uppercase tracking-widest" style="font-size: 0.7rem;">
                            Filter
                        </button>
                        <button onclick="printFilteredLogs()" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-bold text-uppercase tracking-widest d-flex align-items-center gap-2" style="font-size: 0.7rem;">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                            Print
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="table-responsive d-none d-md-block">
                <table class="table table-dark table-hover mb-0 align-middle">
                    <thead>
                        <tr class="bg-secondary bg-opacity-5">
                            <th class="ps-4 py-3 text-secondary text-uppercase small fw-bold tracking-widest">Date & Time</th>
                            <th class="py-3 text-secondary text-uppercase small fw-bold tracking-widest">Particulars</th>
                            <th class="py-3 text-secondary text-uppercase small fw-bold tracking-widest text-center">Unbudgeted</th>
                            <th class="py-3 text-secondary text-uppercase small fw-bold tracking-widest">Charged To</th>
                            <th class="py-3 text-secondary text-uppercase small fw-bold tracking-widest text-center">KM</th>
                            <th class="py-3 text-secondary text-uppercase small fw-bold tracking-widest text-center">HRS</th>
                            <th class="pe-4 py-3 text-secondary text-uppercase small fw-bold tracking-widest text-center">Order ID</th>
                        </tr>
                    </thead>
                    <tbody id="logs-body">
                        <!-- JS Loaded -->
                    </tbody>
                </table>
            </div>

            <!-- Mobile View -->
            <div id="logs-body-mobile" class="d-md-none list-group list-group-flush bg-dark">
                <!-- JS Loaded -->
            </div>

            <div id="loading" class="p-5 text-center">
                <div class="spinner-border text-primary mb-3" role="status"></div>
                <p class="small fw-bold text-secondary text-uppercase tracking-widest">Syncing Data...</p>
            </div>

            <div id="no-more-logs" class="p-4 text-center bg-secondary bg-opacity-5 d-none">
                <p class="small fw-bold text-secondary text-uppercase tracking-widest mb-0">End of History</p>
            </div>
        </div>
    </div>

    <script>
        let page = 1;
        let loading = false;
        let hasMore = true;

        async function fetchSubAccounts(accountId) {
            const subAccountSelect = document.getElementById('sub_account_id');
            subAccountSelect.innerHTML = '<option value="">-- Select Sub Account --</option>';
            
            if (!accountId) {
                subAccountSelect.disabled = true;
                return;
            }

            subAccountSelect.disabled = true;
            subAccountSelect.innerHTML = '<option value="">Loading...</option>';

            try {
                const response = await fetch(`/chargeable-accounts/${accountId}/sub-accounts/json`);
                const subAccounts = await response.json();

                subAccountSelect.innerHTML = '<option value="">-- Select Sub Account --</option>';
                subAccounts.forEach(sub => {
                    const option = document.createElement('option');
                    option.value = sub.id;
                    option.textContent = sub.name;
                    subAccountSelect.appendChild(option);
                });
                
                subAccountSelect.disabled = false;
            } catch (error) {
                console.error('Error fetching sub-accounts:', error);
                subAccountSelect.innerHTML = '<option value="">Error loading sub-accounts</option>';
            }
        }

        const form = document.getElementById('utilization-form');
        const submitBtn = document.getElementById('submit-btn');
        const btnText = document.getElementById('btn-text');
        const btnSpinner = document.getElementById('btn-spinner');

        form.onsubmit = async (e) => {
            e.preventDefault();
            
            // Reset errors
            document.querySelectorAll('[id^="error-"]').forEach(el => {
                el.innerText = '';
                el.classList.add('d-none');
            });

            // Loading state
            submitBtn.disabled = true;
            btnText.innerText = 'Saving...';
            btnSpinner.classList.remove('d-none');

            const formData = new FormData(form);

            try {
                const response = await fetch('{{ route('utilization-entries.store') }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (response.ok) {
                    // Save values to persist
                    const dateVal = document.getElementById('date').value;
                    const startTimeVal = document.getElementById('start_time').value;
                    const endTimeVal = document.getElementById('end_time').value;
                    const driverVal = document.getElementById('driver_operator_name').value;
                    const accountVal = document.getElementById('chargeable_account_id').value;
                    const subAccountVal = document.getElementById('sub_account_id').value;
                    const referenceVal = document.getElementById('reference').value;
                    const calcVal = document.getElementById('calculation_type').value;

                    // Reset form
                    form.reset();

                    // Restore persisted values
                    document.getElementById('date').value = dateVal;
                    document.getElementById('start_time').value = startTimeVal;
                    document.getElementById('end_time').value = endTimeVal;
                    document.getElementById('driver_operator_name').value = driverVal;
                    document.getElementById('chargeable_account_id').value = accountVal;
                    
                    // For sub-account, we need to re-populate if it was cleared by reset
                    if (accountVal) {
                        await fetchSubAccounts(accountVal);
                        document.getElementById('sub_account_id').value = subAccountVal;
                    }

                    document.getElementById('reference').value = referenceVal;
                    document.getElementById('calculation_type').value = calcVal;

                    // Reload table
                    page = 1;
                    hasMore = true;
                    document.getElementById('logs-body').innerHTML = '';
                    document.getElementById('logs-body-mobile').innerHTML = '';
                    loadLogs();
                    
                    alert(data.message);
                } else {
                    // Show errors
                    if (data.errors) {
                        for (const [key, messages] of Object.entries(data.errors)) {
                            const errorEl = document.getElementById(`error-${key}`);
                            if (errorEl) {
                                errorEl.innerText = messages[0];
                                errorEl.classList.remove('d-none');
                            }
                        }
                    } else {
                        alert('An unexpected error occurred.');
                    }
                }
            } catch (error) {
                console.error('Error saving entry:', error);
                alert('Network error. Please try again.');
            } finally {
                submitBtn.disabled = false;
                btnText.innerText = 'Submit Entry';
                btnSpinner.classList.add('d-none');
            }
        };

        async function loadLogs() {
            if (loading || !hasMore) return;
            loading = true;
            document.getElementById('loading').classList.remove('d-none');
            
            const startDate = document.getElementById('filter_start_date').value;
            const endDate = document.getElementById('filter_end_date').value;
            const accountId = document.getElementById('filter_chargeable_account_id').value;
            const orderId = document.getElementById('filter_fuel_order_id').value;

            try {
                let url = `{{ route('assets.logs', $asset) }}?page=${page}`;
                if (startDate) url += `&start_date=${startDate}`;
                if (endDate) url += `&end_date=${endDate}`;
                if (accountId) url += `&chargeable_account_id=${accountId}`;
                if (orderId) url += `&fuel_order_id=${orderId}`;

                const response = await fetch(url);
                const data = await response.json();

                if (page === 1) {
                    document.getElementById('total-logs').innerText = data.total;
                }

                if (data.data.length === 0) {
                    hasMore = false;
                    if (page === 1) {
                        const emptyHtml = '<div class="p-5 text-center text-secondary fw-bold text-uppercase small tracking-widest">No activity found</div>';
                        document.getElementById('logs-body').innerHTML = `<tr><td colspan="7">${emptyHtml}</td></tr>`;
                        document.getElementById('logs-body-mobile').innerHTML = emptyHtml;
                    } else {
                        document.getElementById('no-more-logs').classList.remove('d-none');
                    }
                } else {
                    const body = document.getElementById('logs-body');
                    const bodyMobile = document.getElementById('logs-body-mobile');
                    
                    data.data.forEach(entry => {
                        const dateObj = new Date(entry.date);
                        const formattedDate = dateObj.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                        
                        let operationHoursHtml = '';
                        if (entry.start_time && entry.end_time) {
                            const startParts = entry.start_time.split(':');
                            const endParts = entry.end_time.split(':');
                            if (startParts.length >= 2 && endParts.length >= 2) {
                                const start = new Date();
                                start.setHours(parseInt(startParts[0], 10), parseInt(startParts[1], 10), 0, 0);
                                const end = new Date();
                                end.setHours(parseInt(endParts[0], 10), parseInt(endParts[1], 10), 0, 0);
                                if (end < start) end.setDate(end.getDate() + 1);
                                const diffHrs = (end - start) / (1000 * 60 * 60);
                                operationHoursHtml = `<div class="mt-1"><span class="badge bg-secondary bg-opacity-25 text-secondary border border-secondary border-opacity-25 fw-bold text-uppercase tracking-widest" style="font-size: 0.6rem;">${diffHrs.toFixed(2)} hrs</span></div>`;
                            }
                        }
                        
                        // Desktop Row
                        const row = document.createElement('tr');
                        row.style.cursor = 'pointer';
                        row.onclick = () => window.location.href = `/utilization-entries/${entry.id}`;
                        row.innerHTML = `
                            <td class="ps-4 py-3">
                                <div class="fw-bold text-light small">${formattedDate}</div>
                                <div class="text-primary small fw-bold text-uppercase tracking-widest" style="font-size: 0.65rem;">${entry.start_time || '—'} - ${entry.end_time || '—'}</div>
                                ${operationHoursHtml}
                            </td>
                            <td class="py-3 text-secondary small">${entry.particulars}</td>
                            <td class="py-3 text-center">
                                ${entry.unbudgeted ? '<span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 text-uppercase fw-bold tracking-widest" style="font-size: 0.6rem;">Yes</span>' : '<span class="text-secondary opacity-25">—</span>'}
                            </td>
                            <td class="py-3">
                                <div class="text-secondary small">
                                    ${entry.chargeable_account ? entry.chargeable_account.name : '—'}
                                    <span class="mx-1 opacity-25">|</span>
                                    ${entry.sub_account ? entry.sub_account.name : '—'}
                                </div>
                            </td>
                            <td class="py-3 text-center font-monospace small text-light">${parseFloat(entry.start_kilometer_reading).toLocaleString()} - ${parseFloat(entry.end_kilometer_reading).toLocaleString()}</td>
                            <td class="py-3 text-center font-monospace small text-light">${parseFloat(entry.start_hour_reading).toLocaleString()} - ${parseFloat(entry.end_hour_reading).toLocaleString()}</td>
                            <td class="pe-4 py-3 text-center">
                                ${entry.fuel_order_id ? `<span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 fw-bold" style="font-size: 0.65rem;">#${entry.fuel_order_id}</span>` : '<span class="text-secondary opacity-25">—</span>'}
                            </td>
                        `;
                        body.appendChild(row);

                        // Mobile Card
                        const card = document.createElement('div');
                        card.className = 'list-group-item bg-dark border-secondary border-opacity-10 p-4';
                        card.style.cursor = 'pointer';
                        card.onclick = () => window.location.href = `/utilization-entries/${entry.id}`;
                        card.innerHTML = `
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <div class="fw-bold text-light small">${formattedDate}</div>
                                    <div class="text-primary small fw-bold text-uppercase tracking-widest" style="font-size: 0.65rem;">${entry.start_time || '—'} - ${entry.end_time || '—'}</div>
                                    ${operationHoursHtml}
                                </div>
                                ${entry.fuel_order_id ? `<span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 fw-bold">#${entry.fuel_order_id}</span>` : ''}
                            </div>
                            <p class="small text-secondary mb-2">${entry.particulars} ${entry.unbudgeted ? '<span class="badge bg-danger bg-opacity-10 text-danger ms-2" style="font-size: 0.6rem;">UNBUDGETED</span>' : ''}</p>
                            <p class="text-primary small fw-bold text-uppercase tracking-widest mb-3" style="font-size: 0.6rem;">
                                <span class="text-secondary opacity-50">ACCOUNT:</span> 
                                ${entry.chargeable_account ? entry.chargeable_account.name : '—'} 
                                | 
                                ${entry.sub_account ? entry.sub_account.name : '—'}
                            </p>
                            <div class="row g-2 pt-3 border-top border-secondary border-opacity-10">
                                <div class="col-6">
                                    <p class="text-secondary text-uppercase fw-bold tracking-widest mb-1" style="font-size: 0.55rem;">Kilometers</p>
                                    <p class="small font-monospace text-light mb-0">${parseFloat(entry.start_kilometer_reading).toLocaleString()} - ${parseFloat(entry.end_kilometer_reading).toLocaleString()}</p>
                                </div>
                                <div class="col-6 text-end">
                                    <p class="text-secondary text-uppercase fw-bold tracking-widest mb-1" style="font-size: 0.55rem;">Hours</p>
                                    <p class="small font-monospace text-light mb-0">${parseFloat(entry.start_hour_reading).toLocaleString()} - ${parseFloat(entry.end_hour_reading).toLocaleString()}</p>
                                </div>
                            </div>
                        `;
                        bodyMobile.appendChild(card);
                    });
                    page++;
                    if (!data.next_page_url) {
                        hasMore = false;
                        document.getElementById('no-more-logs').classList.remove('d-none');
                    }
                }
            } catch (error) {
                console.error('Error Syncing:', error);
            } finally {
                loading = false;
                document.getElementById('loading').classList.add('d-none');
            }
        }

        function applyFilter() {
            page = 1;
            hasMore = true;
            document.getElementById('logs-body').innerHTML = '';
            document.getElementById('logs-body-mobile').innerHTML = '';
            document.getElementById('no-more-logs').classList.add('d-none');
            loadLogs();
        }

        function printFilteredLogs() {
            const startDate = document.getElementById('filter_start_date').value;
            const endDate = document.getElementById('filter_end_date').value;
            const accountId = document.getElementById('filter_chargeable_account_id').value;
            const orderId = document.getElementById('filter_fuel_order_id').value;

            let url = `{{ route('assets.logs.print', $asset) }}?`;
            const params = new URLSearchParams();
            if (startDate) params.append('start_date', startDate);
            if (endDate) params.append('end_date', endDate);
            if (accountId) params.append('chargeable_account_id', accountId);
            if (orderId) params.append('fuel_order_id', orderId);

            window.open(url + params.toString(), '_blank');
        }

        window.onscroll = function() {
            if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight - 500) {
                loadLogs();
            }
        };

        loadLogs();
    </script>
</x-app-layout>
