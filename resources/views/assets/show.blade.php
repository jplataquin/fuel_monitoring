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
        @if (session('status'))
            <div class="alert alert-success bg-success bg-opacity-10 border-success border-opacity-20 text-success d-flex align-items-center mb-4 rounded-3" role="alert">
                <svg class="me-2" width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <div class="fw-bold small">{{ session('status') }}</div>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger bg-danger bg-opacity-10 border-danger border-opacity-20 text-danger d-flex align-items-center mb-4 rounded-3" role="alert">
                <svg class="me-2" width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
                <div class="fw-bold small">{{ session('error') }}</div>
            </div>
        @endif

        <div class="row g-4 mb-5">
            <!-- Asset Specs -->
            <div class="col-lg-8">
                <div class="card h-100 bg-dark border-secondary border-opacity-25">
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
                                    <p class="text-secondary text-uppercase small fw-bold tracking-widest mb-1" style="font-size: 0.75rem;">Equipment Type</p>
                                    <p class="h6 fw-bold text-light mb-0">{{ $asset->assetType->name }}</p>
                                </div>
                            </div>
                            <div class="col d-flex align-items-start gap-3">
                                <div class="bg-primary bg-opacity-10 p-2 rounded-3 text-primary">
                                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" /></svg>
                                </div>
                                <div>
                                    <p class="text-secondary text-uppercase small fw-bold tracking-widest mb-1" style="font-size: 0.75rem;">Plate Number</p>
                                    <p class="h6 fw-bold text-light font-monospace mb-0">{{ $asset->plate_no ?? 'UNASSIGNED' }}</p>
                                </div>
                            </div>
                            <div class="col d-flex align-items-start gap-3">
                                <div class="bg-primary bg-opacity-10 p-2 rounded-3 text-primary">
                                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
                                </div>
                                <div>
                                    <p class="text-secondary text-uppercase small fw-bold tracking-widest mb-1" style="font-size: 0.75rem;">Tank Capacity</p>
                                    <p class="h6 fw-bold text-light mb-0">{{ number_format($asset->tank_capacity, 2) }} <span class="small text-secondary fw-medium ms-1">LITERS</span></p>
                                </div>
                            </div>
                            <div class="col d-flex align-items-start gap-3">
                                <div class="bg-primary bg-opacity-10 p-2 rounded-3 text-primary">
                                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
                                </div>
                                <div>
                                    <p class="text-secondary text-uppercase small fw-bold tracking-widest mb-1" style="font-size: 0.75rem;">Fuel Type</p>
                                    <p class="h6 fw-bold text-light text-uppercase mb-0">{{ $asset->fuel_type ?? 'Diesel' }}</p>
                                </div>
                            </div>
                            <div class="col d-flex align-items-start gap-3">
                                <div class="bg-primary bg-opacity-10 p-2 rounded-3 text-primary">
                                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                                </div>
                                <div>
                                    <p class="text-secondary text-uppercase small fw-bold tracking-widest mb-1" style="font-size: 0.75rem;">Factor (KM)</p>
                                    <p class="h6 fw-bold text-light mb-0">{{ $asset->fuel_factor_km ?? '0.00' }} <span class="small text-secondary fw-medium ms-1">KM/L</span></p>
                                </div>
                            </div>
                            <div class="col d-flex align-items-start gap-3">
                                <div class="bg-primary bg-opacity-10 p-2 rounded-3 text-primary">
                                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </div>
                                <div>
                                    <p class="text-secondary text-uppercase small fw-bold tracking-widest mb-1" style="font-size: 0.75rem;">Factor (HR)</p>
                                    <p class="h6 fw-bold text-light mb-0">{{ $asset->fuel_factor_hr ?? '0.00' }} <span class="small text-secondary fw-medium ms-1">L/HR</span></p>
                                </div>
                            </div>
                            <div class="col d-flex align-items-start gap-3">
                                <div class="bg-primary bg-opacity-10 p-2 rounded-3 text-primary">
                                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A2 2 0 013 15.487V4.513a2 2 0 011.553-1.943L9 1.5l5.447 2.724A2 2 0 0116 6.164v10.973a2 2 0 01-1.553 1.943L9 21.5z" /></svg>
                                </div>
                                <div>
                                    <p class="text-secondary text-uppercase small fw-bold tracking-widest mb-1" style="font-size: 0.75rem;">Last Odometer</p>
                                    <p class="h6 fw-bold text-light mb-0">{{ number_format($asset->last_kilometer_reading, 2) }} <span class="small text-secondary fw-medium ms-1">KM</span></p>
                                </div>
                            </div>
                            <div class="col d-flex align-items-start gap-3">
                                <div class="bg-primary bg-opacity-10 p-2 rounded-3 text-primary">
                                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </div>
                                <div>
                                    <p class="text-secondary text-uppercase small fw-bold tracking-widest mb-1" style="font-size: 0.75rem;">Last Engine Hours</p>
                                    <p class="h6 fw-bold text-light mb-0">{{ number_format($asset->last_engine_hours, 2) }} <span class="small text-secondary fw-medium ms-1">HRS</span></p>
                                </div>
                            </div>
                            <div class="col d-flex align-items-start gap-3">
                                <div class="bg-primary bg-opacity-10 p-2 rounded-3 text-primary">
                                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </div>
                                <div>
                                    <p class="text-secondary text-uppercase small fw-bold tracking-widest mb-1" style="font-size: 0.75rem;">Last Time</p>
                                    <p class="h6 fw-bold text-light mb-0">{{ $asset->last_time ? date('H:i', strtotime($asset->last_time)) : 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="col d-flex align-items-start gap-3">
                                <div class="bg-primary bg-opacity-10 p-2 rounded-3 text-primary">
                                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                </div>
                                <div>
                                    <p class="text-secondary text-uppercase small fw-bold tracking-widest mb-1" style="font-size: 0.75rem;">Last Date</p>
                                    <p class="h6 fw-bold text-light mb-0">{{ $asset->last_date ? date('M d, Y', strtotime($asset->last_date)) : 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card h-100 bg-dark border-secondary border-opacity-25 text-center overflow-hidden position-relative">
                    <div class="card-body p-5 d-flex flex-column justify-content-center position-relative z-1">
                        <p class="text-secondary text-uppercase small fw-bold tracking-widest mb-4">Lifecycle Stats</p>
                        <div class="mb-2">
                            <p class="display-3 fw-bold text-primary mb-0" id="total-logs">0</p>
                        </div>
                        <p class="small fw-bold text-light text-opacity-75 text-uppercase tracking-widest">Total Logs</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Register Utilization Form Modal -->
        <div class="modal fade" id="utilizationModal" tabindex="-1" aria-labelledby="utilizationModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content bg-dark border-0">
                    <div class="modal-header border-secondary border-opacity-25 px-4 px-md-5 py-4 bg-dark">
                        <h4 class="modal-title h4 fw-bold text-light d-flex align-items-center" id="utilizationModalLabel">
                            <span class="bg-primary p-2 rounded-3 me-3 text-white d-inline-flex align-items-center">
                                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                            </span>
                            Register Utilization
                        </h4>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4 p-md-5 bg-dark">
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

                                <div class="col-md-6">
                                    <label for="calculation_type" class="form-label text-secondary text-uppercase small fw-bold tracking-widest ps-1 mb-2">Calculation Type</label>
                                    <select id="calculation_type" name="calculation_type" class="form-select bg-dark bg-opacity-25 border-secondary border-opacity-50 text-light focus-ring focus-ring-primary" required>
                                        <option value="">-- Select Calculation Type --</option>
                                        <option value="Kilometer Reading">Kilometer Reading</option>
                                        <option value="Hour Reading">Hour Reading</option>
                                        <option value="Timeframe">Timeframe</option>
                                        <option value="Actual Hours">Actual Hours</option>
                                    </select>
                                    <p class="text-danger small fw-bold mt-1 ps-1 d-none" id="error-calculation_type"></p>
                                </div>
                                <div class="col-md-6">
                                    <label for="unbudgeted" class="form-label text-secondary text-uppercase small fw-bold tracking-widest ps-1 mb-2">Unbudgeted</label>
                                    <select id="unbudgeted" name="unbudgeted" class="form-select bg-dark bg-opacity-25 border-secondary border-opacity-50 text-light focus-ring focus-ring-primary" required onchange="toggleSubAccount()">
                                        <option value="0">No</option>
                                        <option value="1">Yes</option>
                                    </select>
                                    <p class="text-danger small fw-bold mt-1 ps-1 d-none" id="error-unbudgeted"></p>
                                </div>

                                 <div class="col-md-6">
                                    <label for="chargeable_account_id" class="form-label text-secondary text-uppercase small fw-bold tracking-widest ps-1 mb-2">Charged To</label>
                                    <select id="chargeable_account_id" name="chargeable_account_id" class="form-select bg-dark bg-opacity-25 border-secondary border-opacity-50 text-light focus-ring focus-ring-primary" required onchange="fetchSubAccounts(this.value)">
                                        <option value="">-- Select Account --</option>
                                        @foreach($chargeableAccounts as $account)
                                            <option value="{{ $account->id }}"
                                                    data-classification="{{ $account->classification }}"
                                                    data-start-date="{{ $account->start_date ? $account->start_date->format('Y-m-d') : '' }}"
                                                    data-end-date="{{ $account->end_date ? $account->end_date->format('Y-m-d') : '' }}">
                                                {{ $account->name }}
                                                @if($account->classification === 'Scoped')
                                                    ({{ $account->start_date ? $account->start_date->format('M d, Y') : 'N/A' }} - {{ $account->end_date ? $account->end_date->format('M d, Y') : 'N/A' }})
                                                @endif
                                            </option>
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
                                
                                <div class="col-12">
                                    <label for="particulars" class="form-label text-secondary text-uppercase small fw-bold tracking-widest ps-1 mb-2">Particulars / Mission</label>
                                    <textarea id="particulars" name="particulars" class="form-control bg-dark bg-opacity-25 border-secondary border-opacity-50 text-light focus-ring focus-ring-primary" required placeholder="Describe the activity..."></textarea>
                                    <p class="text-danger small fw-bold mt-1 ps-1 d-none" id="error-particulars"></p>
                                </div>

                                <div class="col-md-3">
                                    <label for="date" class="form-label text-secondary text-uppercase small fw-bold tracking-widest ps-1 mb-2">Date</label>
                                    <input id="date" name="date" type="date" class="form-control bg-dark bg-opacity-25 border-secondary border-opacity-50 text-light focus-ring focus-ring-primary" value="{{ date('Y-m-d') }}" required>
                                    <div id="date-scope-error" class="text-danger small fw-bold mt-1 ps-1 d-none"></div>
                                    <p class="text-danger small fw-bold mt-1 ps-1 d-none" id="error-date"></p>
                                </div>
                                <div class="col-md-3">
                                    <label for="start_time" class="form-label text-secondary text-uppercase small fw-bold tracking-widest ps-1 mb-2">Start Time</label>
                                    <input id="start_time" name="start_time" type="time" class="form-control bg-dark bg-opacity-25 border-secondary border-opacity-50 text-light focus-ring focus-ring-primary" value="{{ date('H:i') }}" required>
                                    <p class="text-danger small fw-bold mt-1 ps-1 d-none" id="error-start_time"></p>
                                </div>
                                <div class="col-md-3">
                                    <label for="end_time" class="form-label text-secondary text-uppercase small fw-bold tracking-widest ps-1 mb-2">End Time</label>
                                    <input id="end_time" name="end_time" type="time" class="form-control bg-dark bg-opacity-25 border-secondary border-opacity-50 text-light focus-ring focus-ring-primary" value="{{ date('H:i', strtotime('+1 hour')) }}" required>
                                    <p class="text-danger small fw-bold mt-1 ps-1 d-none" id="error-end_time"></p>
                                </div>
                                <div class="col-md-3">
                                    <label for="actual_hours" class="form-label text-secondary text-uppercase small fw-bold tracking-widest ps-1 mb-2">Hours</label>
                                    <input id="actual_hours" name="actual_hours" type="number" step="0.01" class="form-control bg-dark bg-opacity-25 border-secondary border-opacity-50 text-light focus-ring focus-ring-primary" placeholder="0.00" onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode == 46">
                                    <p class="text-danger small fw-bold mt-1 ps-1 d-none" id="error-actual_hours"></p>
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
                                <button type="button" class="btn btn-outline-secondary rounded-pill px-4 py-3 fw-bold text-uppercase tracking-widest me-3" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" id="submit-btn" class="btn btn-primary rounded-pill px-5 py-3 fw-bold text-uppercase tracking-widest shadow-sm">
                                    <span id="btn-text">Submit Entry</span>
                                    <div id="btn-spinner" class="spinner-border spinner-border-sm ms-2 d-none" role="status"></div>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Utilization Logs -->
        <div class="card bg-dark border-secondary border-opacity-25 overflow-hidden">
            <div class="card-header bg-secondary bg-opacity-10 py-4 px-4 border-bottom border-secondary border-opacity-25">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-4">
                    <div class="d-flex align-items-center gap-2">
                        <h3 class="h5 fw-bold text-light mb-0 tracking-tight">{{ __('Utilization Logs') }}</h3>
                        <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold text-uppercase tracking-widest" data-bs-toggle="modal" data-bs-target="#utilizationModal" style="font-size: 0.75rem;">
                            <svg width="12" height="12" class="me-1 d-inline-block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            Register
                        </button>
                        <a href="{{ route('assets.utilization-entries.bulk-upload', $asset) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold text-uppercase tracking-widest d-inline-flex align-items-center" style="font-size: 0.75rem;">
                            <svg width="12" height="12" class="me-1 d-inline-block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            Bulk Upload
                        </a>
                    </div>
                    
                    <div class="d-flex flex-wrap align-items-center gap-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-secondary text-uppercase small fw-bold tracking-widest" style="font-size: 0.75rem;">From</span>
                            <input id="filter_start_date" type="date" class="form-control form-control-sm bg-dark border-secondary border-opacity-50 text-light" style="width: 130px;">
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-secondary text-uppercase small fw-bold tracking-widest" style="font-size: 0.75rem;">To</span>
                            <input id="filter_end_date" type="date" class="form-control form-control-sm bg-dark border-secondary border-opacity-50 text-light" style="width: 130px;">
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-secondary text-uppercase small fw-bold tracking-widest" style="font-size: 0.75rem;">Account</span>
                            <select id="filter_chargeable_account_id" class="form-select form-select-sm bg-dark border-secondary border-opacity-50 text-light" style="width: 150px;">
                                <option value="">All</option>
                                @foreach($chargeableAccounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-secondary text-uppercase small fw-bold tracking-widest" style="font-size: 0.75rem;">Order ID</span>
                            <input id="filter_fuel_order_id" type="number" class="form-control form-control-sm bg-dark border-secondary border-opacity-50 text-light" style="width: 80px;" placeholder="ID">
                        </div>
                        <button onclick="applyFilter()" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold text-uppercase tracking-widest" style="font-size: 0.75rem;">
                            Filter
                        </button>
                        <button onclick="printFilteredLogs()" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-bold text-uppercase tracking-widest d-flex align-items-center gap-2" style="font-size: 0.75rem;">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                            Print
                        </button>
                    </div>
                </div>
            </div>
            
            <div id="table-scroll-container" class="table-responsive d-none d-md-block" style="cursor: grab; overflow-x: auto; -webkit-overflow-scrolling: touch;">
                <table class="table table-dark table-hover mb-0 align-middle" style="min-width:1500px">
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

            <div id="infinite-scroll-sentinel" class="py-1"></div>

            <div id="no-more-logs" class="p-4 text-center bg-secondary bg-opacity-5 d-none">
                <p class="small fw-bold text-dark text-uppercase tracking-widest mb-0">End of History</p>
            </div>
        </div>
    </div>

    <script>
        let page = 1;
        let loading = false;
        let hasMore = true;

        function truncateText(text, limit = 50) {
            if (!text) return '—';
            return text.length > limit ? text.substring(0, limit) + '...' : text;
        }

        function toggleSubAccount() {
            const unbudgetedSelect = document.getElementById('unbudgeted');
            const subAccountSelect = document.getElementById('sub_account_id');
            
            if (unbudgetedSelect.value === '1') {
                subAccountSelect.value = '';
                subAccountSelect.disabled = true;
                subAccountSelect.required = false;
            } else {
                const accountId = document.getElementById('chargeable_account_id').value;
                if (accountId) {
                    subAccountSelect.disabled = false;
                }
                subAccountSelect.required = true;
            }
        }

        async function fetchSubAccounts(accountId) {
            const subAccountSelect = document.getElementById('sub_account_id');
            const unbudgetedSelect = document.getElementById('unbudgeted');
            
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
                    option.textContent = sub.display_name;
                    subAccountSelect.appendChild(option);
                });
                
                if (unbudgetedSelect.value !== '1') {
                    subAccountSelect.disabled = false;
                }
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

            // Temporarily re-enable disabled fields so they are compiled in FormData
            const disabledInputs = form.querySelectorAll('input:disabled, select:disabled, textarea:disabled');
            disabledInputs.forEach(el => el.disabled = false);

            const formData = new FormData(form);

            // Re-disable them immediately after capturing FormData
            disabledInputs.forEach(el => el.disabled = true);

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
                    toggleSubAccount();

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
                    
                    // Re-validate restored values
                    validateDateScope();
                    
                    // Close Modal
                    const modalEl = document.getElementById('utilizationModal');
                    const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                    if (modalInstance) {
                        modalInstance.hide();
                    }

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
                                operationHoursHtml = `<div class="mt-1"><span class="badge bg-secondary bg-opacity-25 text-secondary border border-secondary border-opacity-25 fw-bold text-uppercase tracking-widest" style="font-size: 0.75rem;">${diffHrs.toFixed(2)} hrs</span></div>`;
                            }
                        }
                        
                        const particularsTruncated = truncateText(entry.particulars, 50);
                        
                        let accountText = entry.chargeable_account ? entry.chargeable_account.name : '';
                        if (entry.sub_account) {
                            accountText += (accountText ? ' | ' : '') + entry.sub_account.name;
                        }
                        if (!accountText) {
                            accountText = '—';
                        }
                        const chargedToTruncated = truncateText(accountText, 50);

                        // Desktop Row
                        const row = document.createElement('tr');
                        row.style.cursor = 'pointer';
                        row.onclick = (e) => {
                            const container = document.getElementById('table-scroll-container');
                            if (container && container.isDragging) {
                                e.preventDefault();
                                return;
                            }
                            window.location.href = `/utilization-entries/${entry.id}`;
                        };
                        row.innerHTML = `
                            <td class="ps-4 py-3">
                                <div class="fw-bold text-light small">${formattedDate}</div>
                                <div class="text-primary small fw-bold text-uppercase tracking-widest" style="font-size: 0.75rem;">${entry.start_time || '—'} - ${entry.end_time || '—'}</div>
                                ${operationHoursHtml}
                            </td>
                            <td class="py-3 text-secondary small" title="${entry.particulars || ''}">${particularsTruncated}</td>
                            <td class="py-3 text-center">
                                ${entry.unbudgeted ? '<span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 text-uppercase fw-bold tracking-widest" style="font-size: 0.75rem;">Yes</span>' : '<span class="text-secondary opacity-25">—</span>'}
                            </td>
                            <td class="py-3" title="${accountText !== '—' ? accountText : ''}">
                                <div class="text-secondary small">
                                    ${chargedToTruncated}
                                </div>
                            </td>
                            <td class="py-3 text-center font-monospace small text-light">${parseFloat(entry.start_kilometer_reading).toLocaleString()} - ${parseFloat(entry.end_kilometer_reading).toLocaleString()}</td>
                            <td class="py-3 text-center font-monospace small text-light">${parseFloat(entry.start_hour_reading).toLocaleString()} - ${parseFloat(entry.end_hour_reading).toLocaleString()}</td>
                            <td class="pe-4 py-3 text-center">
                                ${entry.fuel_order_id ? `<span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 fw-bold" style="font-size: 0.75rem;">#${entry.fuel_order_id}</span>` : '<span class="text-secondary opacity-25">—</span>'}
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
                                    <div class="text-primary small fw-bold text-uppercase tracking-widest" style="font-size: 0.75rem;">${entry.start_time || '—'} - ${entry.end_time || '—'}</div>
                                    ${operationHoursHtml}
                                </div>
                                ${entry.fuel_order_id ? `<span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 fw-bold">#${entry.fuel_order_id}</span>` : ''}
                            </div>
                            <p class="small text-secondary mb-2" title="${entry.particulars || ''}">${particularsTruncated} ${entry.unbudgeted ? '<span class="badge bg-danger bg-opacity-10 text-danger ms-2" style="font-size: 0.75rem;">UNBUDGETED</span>' : ''}</p>
                            <p class="text-primary small fw-bold text-uppercase tracking-widest mb-3" style="font-size: 0.75rem;" title="${accountText !== '—' ? accountText : ''}">
                                <span class="text-secondary opacity-50">ACCOUNT:</span> 
                                ${chargedToTruncated}
                            </p>
                            <div class="row g-2 pt-3 border-top border-secondary border-opacity-10">
                                <div class="col-6">
                                    <p class="text-secondary text-uppercase fw-bold tracking-widest mb-1" style="font-size: 0.75rem;">Kilometers</p>
                                    <p class="small font-monospace text-light mb-0">${parseFloat(entry.start_kilometer_reading).toLocaleString()} - ${parseFloat(entry.end_kilometer_reading).toLocaleString()}</p>
                                </div>
                                <div class="col-6 text-end">
                                    <p class="text-secondary text-uppercase fw-bold tracking-widest mb-1" style="font-size: 0.75rem;">Hours</p>
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

        // IntersectionObserver for lazy loading infinite scroll
        const sentinel = document.getElementById('infinite-scroll-sentinel');
        const observer = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting && !loading && hasMore) {
                loadLogs();
            }
        }, {
            root: null, // use the viewport
            rootMargin: '200px', // start loading when the sentinel is within 200px of the viewport
            threshold: 0.1
        });

        if (sentinel) {
            observer.observe(sentinel);
        }

        function parseLocalDate(dateStr) {
            if (!dateStr) return null;
            const parts = dateStr.split('-');
            if (parts.length !== 3) return null;
            return new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
        }

        function validateDateScope() {
            const dateInput = document.getElementById('date');
            const accountSelect = document.getElementById('chargeable_account_id');
            const errorDiv = document.getElementById('date-scope-error');
            const submitBtn = document.getElementById('submit-btn');
            
            if (!dateInput.value || !accountSelect.value) {
                errorDiv.classList.add('d-none');
                errorDiv.textContent = '';
                submitBtn.disabled = false;
                return;
            }

            const selectedOption = accountSelect.options[accountSelect.selectedIndex];
            const classification = selectedOption.getAttribute('data-classification');
            const startDateStr = selectedOption.getAttribute('data-start-date');
            const endDateStr = selectedOption.getAttribute('data-end-date');

            if (classification && classification.toLowerCase() === 'scoped') {
                const selectedDate = parseLocalDate(dateInput.value);
                if (!selectedDate) return;
                
                let isInvalid = false;
                let message = '';

                if (startDateStr) {
                    const startDate = parseLocalDate(startDateStr);
                    if (startDate && selectedDate < startDate) {
                        isInvalid = true;
                        message = `The date must be on or after ${formatDate(startDateStr)}.`;
                    }
                }

                if (endDateStr && !isInvalid) {
                    const endDate = parseLocalDate(endDateStr);
                    if (endDate && selectedDate > endDate) {
                        isInvalid = true;
                        message = `The date must be on or before ${formatDate(endDateStr)}.`;
                    }
                }

                if (isInvalid) {
                    errorDiv.textContent = message;
                    errorDiv.classList.remove('d-none');
                    submitBtn.disabled = true;
                } else {
                    errorDiv.classList.add('d-none');
                    errorDiv.textContent = '';
                    submitBtn.disabled = false;
                }
            } else {
                errorDiv.classList.add('d-none');
                errorDiv.textContent = '';
                submitBtn.disabled = false;
            }
        }

        function formatDate(dateStr) {
            const date = parseLocalDate(dateStr);
            if (!date) return '';
            return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
        }

        function updateFieldStates() {
            const calculationType = document.getElementById('calculation_type').value;
            const startOdo = document.getElementById('start_kilometer_reading');
            const endOdo = document.getElementById('end_kilometer_reading');
            const startEngine = document.getElementById('start_hour_reading');
            const endEngine = document.getElementById('end_hour_reading');
            const actualHours = document.getElementById('actual_hours');
            const startTime = document.getElementById('start_time');
            const endTime = document.getElementById('end_time');

            const setRequired = (el, isRequired) => {
                if (el) {
                    el.required = isRequired;
                    const label = document.querySelector(`label[for="${el.id}"]`);
                    if (label) {
                        let ast = label.querySelector('.req-ast');
                        if (isRequired) {
                            if (!ast) {
                                label.insertAdjacentHTML('beforeend', ' <span class="text-danger req-ast">*</span>');
                            }
                        } else {
                            if (ast) {
                                ast.remove();
                            }
                        }
                    }
                }
            };

            // Reset all to not required first
            setRequired(startOdo, false);
            setRequired(endOdo, false);
            setRequired(startEngine, false);
            setRequired(endEngine, false);
            setRequired(actualHours, false);
            setRequired(startTime, false);
            setRequired(endTime, false);

            if (calculationType === 'Kilometer Reading') {
                setRequired(startOdo, true);
                setRequired(endOdo, true);
                setRequired(startTime, true);
                setRequired(endTime, true);
            } else if (calculationType === 'Hour Reading') {
                setRequired(startEngine, true);
                setRequired(endEngine, true);
                setRequired(startTime, true);
                setRequired(endTime, true);
            } else if (calculationType === 'Timeframe') {
                setRequired(startTime, true);
                setRequired(endTime, true);
            } else if (calculationType === 'Actual Hours') {
                setRequired(actualHours, true);
            } else {
                setRequired(startOdo, true);
                setRequired(endOdo, true);
                setRequired(startEngine, true);
                setRequired(endEngine, true);
                setRequired(actualHours, true);
                setRequired(startTime, true);
                setRequired(endTime, true);
            }
        }

        const dateInput = document.getElementById('date');
        const accountSelect = document.getElementById('chargeable_account_id');
        const calculationTypeSelect = document.getElementById('calculation_type');
        
        dateInput.addEventListener('input', validateDateScope);
        dateInput.addEventListener('change', validateDateScope);
        accountSelect.addEventListener('change', validateDateScope);
        calculationTypeSelect.addEventListener('change', updateFieldStates);

        toggleSubAccount();
        loadLogs();
        validateDateScope();
        updateFieldStates();

        // Click-and-drag horizontal scrolling
        const slider = document.getElementById('table-scroll-container');
        if (slider) {
            let isDown = false;
            let startX;
            let scrollLeft;

            slider.addEventListener('mousedown', (e) => {
                isDown = true;
                slider.style.cursor = 'grabbing';
                slider.style.userSelect = 'none';
                startX = e.pageX - slider.offsetLeft;
                scrollLeft = slider.scrollLeft;
                slider.isDragging = false;
            });

            slider.addEventListener('mouseleave', () => {
                isDown = false;
                slider.style.cursor = 'grab';
                slider.style.removeProperty('user-select');
            });

            slider.addEventListener('mouseup', () => {
                isDown = false;
                slider.style.cursor = 'grab';
                slider.style.removeProperty('user-select');
            });

            slider.addEventListener('mousemove', (e) => {
                if (!isDown) return;
                e.preventDefault();
                const x = e.pageX - slider.offsetLeft;
                const walk = (x - startX) * 1.5; // Scroll speed multiplier
                if (Math.abs(walk) > 5) {
                    slider.isDragging = true;
                }
                slider.scrollLeft = scrollLeft - walk;
            });
        }
    </script>
</x-app-layout>
