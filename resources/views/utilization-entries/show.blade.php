<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h2 fw-bold text-light mb-0 d-flex align-items-center gap-2">
                {{ __('Entry Summary') }}
                @if($utilizationEntry->trashed())
                    <span class="badge bg-danger bg-opacity-20 text-danger border border-danger border-opacity-20 rounded-pill" style="font-size: 11px; padding: 4px 10px;">Deleted</span>
                @endif
            </h2>
            <div class="d-flex align-items-center gap-2">
                @if(in_array(Auth::user()->role, ['administrator', 'moderator']) && !$utilizationEntry->trashed())
                    <form action="{{ route('utilization-entries.destroy', $utilizationEntry) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-link p-2 text-danger bg-danger bg-opacity-10 rounded-circle border-0" onclick="return confirm('Are you sure you want to soft delete this entry?')" title="Delete Entry">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                @endif
                
                @php
                    $canEdit = (in_array(Auth::user()->role, ['administrator', 'moderator']) || 
                               (Auth::user()->role === 'data_logger' && $utilizationEntry->created_at->diffInMinutes(now()) <= 5)) && !$utilizationEntry->trashed();
                @endphp

                @if($canEdit)
                    <a href="{{ route('utilization-entries.edit', $utilizationEntry) }}" class="btn btn-link p-2 text-primary bg-primary bg-opacity-10 rounded-circle border-0" title="Edit Entry">
                        <i class="bi bi-pencil-square"></i>
                    </a>
                @endif

                <a href="{{ route('assets.show', $utilizationEntry->asset_id) }}" class="btn btn-outline-secondary btn-sm fw-bold text-uppercase tracking-widest ms-2">
                    {{ __('Exit') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-5">
        <div class="container-xl">
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

            <div class="card bg-dark border-secondary border-opacity-25 rounded-4 shadow-lg overflow-hidden">
                <div class="card-body p-4 p-md-5">
                    <div class="row g-5">
                        <div class="col-12 col-lg-6">
                            <div class="mb-5">
                                <p class="small fw-bold text-primary text-uppercase tracking-widest mb-3">Asset Identity</p>
                                <div class="row g-4">
                                    <div class="col-12">
                                        <p class="h3 fw-bold text-white mb-0">{{ $utilizationEntry->asset->fleet_no }}</p>
                                        <p class="small fw-bold text-secondary text-uppercase tracking-widest">{{ $utilizationEntry->asset->plate_no ?? 'No Plate' }}</p>
                                    </div>
                                    <div class="col-12">
                                        <p class="small fw-bold text-secondary text-uppercase tracking-widest mb-1">Utilization Date</p>
                                        <p class="h6 fw-bold text-white">{{ $utilizationEntry->date->format('M d, Y') }}</p>
                                    </div>
                                    <div class="col-6">
                                        <p class="small fw-bold text-secondary text-uppercase tracking-widest mb-1">Start Time</p>
                                        <p class="h6 fw-bold text-white text-uppercase">{{ $utilizationEntry->start_time ? $utilizationEntry->start_time->format('H:i') : '—' }}</p>
                                    </div>
                                    <div class="col-6">
                                        <p class="small fw-bold text-secondary text-uppercase tracking-widest mb-1">End Time</p>
                                        <p class="h6 fw-bold text-white text-uppercase">{{ $utilizationEntry->end_time ? $utilizationEntry->end_time->format('H:i') : 'N/A' }}</p>
                                    </div>
                                    
                                    @php
                                        $operationHours = null;
                                        if ($utilizationEntry->end_time && $utilizationEntry->start_time) {
                                            $start = \Carbon\Carbon::parse($utilizationEntry->start_time);
                                            $end = \Carbon\Carbon::parse($utilizationEntry->end_time);
                                            if ($end->lessThan($start)) {
                                                $end->addDay();
                                            }
                                            $operationHours = $start->diffInMinutes($end) / 60;
                                        }
                                    @endphp
                                    
                                    @if($operationHours !== null)
                                    <div class="col-12">
                                        <p class="small fw-bold text-secondary text-uppercase tracking-widest mb-1">Actual Operation Hours</p>
                                        <p class="h6 fw-bold text-primary">{{ number_format($operationHours, 2) }} <span class="small text-uppercase text-secondary ms-1">HRS</span></p>
                                    </div>
                                    @endif
                                    
                                    @if($utilizationEntry->actual_hours !== null)
                                    <div class="col-12">
                                        <p class="small fw-bold text-secondary text-uppercase tracking-widest mb-1">Logged Actual Hours</p>
                                        <p class="h6 fw-bold text-primary">{{ number_format($utilizationEntry->actual_hours, 2) }} <span class="small text-uppercase text-secondary ms-1">HRS</span></p>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <div class="mb-4">
                                <p class="small fw-bold text-secondary text-uppercase tracking-widest mb-2">Reference</p>
                                <div class="bg-dark bg-opacity-50 p-3 rounded-3 border border-secondary border-opacity-25">
                                    <p class="mb-0 text-white font-monospace">{{ $utilizationEntry->reference }}</p>
                                </div>
                            </div>

                            <div class="mb-4">
                                <p class="small fw-bold text-secondary text-uppercase tracking-widest mb-2">Activity / Mission</p>
                                <div class="bg-dark bg-opacity-50 p-3 rounded-3 border border-secondary border-opacity-25">
                                    <p class="mb-0 text-white">{{ $utilizationEntry->particulars }}</p>
                                </div>
                            </div>

                            <div class="mb-4">
                                <p class="small fw-bold text-secondary text-uppercase tracking-widest mb-2">Personnel In-Charge</p>
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                                        <i class="bi bi-person text-primary"></i>
                                    </div>
                                    <p class="h6 fw-bold text-white mb-0">{{ $utilizationEntry->driver_operator_name }}</p>
                                </div>
                            </div>

                            <div class="mb-4">
                                <p class="small fw-bold text-secondary text-uppercase tracking-widest mb-2">Charged To</p>
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                                        <i class="bi bi-credit-card text-primary"></i>
                                    </div>
                                    <p class="h6 fw-bold text-white mb-0">{{ $utilizationEntry->chargeableAccount->name ?? '—' }}</p>
                                </div>
                            </div>

                            <div class="mb-4">
                                <p class="small fw-bold text-secondary text-uppercase tracking-widest mb-2">Sub Account</p>
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                                        <i class="bi bi-building text-primary"></i>
                                    </div>
                                    <p class="h6 fw-bold text-white mb-0">{{ $utilizationEntry->subAccount->display_name ?? '—' }}</p>
                                </div>
                            </div>

                            <div class="mb-4">
                                <p class="small fw-bold text-secondary text-uppercase tracking-widest mb-2">Budget Status</p>
                                <span class="badge rounded-pill {{ $utilizationEntry->unbudgeted ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' }} px-3 py-2 text-uppercase tracking-widest">
                                    {{ $utilizationEntry->unbudgeted ? 'Unbudgeted' : 'Budgeted' }}
                                </span>
                            </div>

                            @if($utilizationEntry->remarks)
                            <div class="mb-4">
                                <p class="small fw-bold text-secondary text-uppercase tracking-widest mb-2">Remarks</p>
                                <div class="bg-dark bg-opacity-50 p-3 rounded-3 border border-secondary border-opacity-25">
                                    <p class="mb-0 text-white small whitespace-pre-line">{{ $utilizationEntry->remarks }}</p>
                                </div>
                            </div>
                            @endif
                        </div>

                        <div class="col-12 col-lg-6">
                            <div class="bg-secondary bg-opacity-10 p-4 rounded-4 border border-secondary border-opacity-25">
                                <div class="row g-4 mb-4">
                                    <div class="col-6">
                                        <div class="bg-dark p-3 rounded-3 border border-secondary border-opacity-25 h-100">
                                            <p class="small fw-bold text-primary text-uppercase tracking-widest mb-2">Start KM</p>
                                            <p class="h4 fw-bold text-white mb-0">{{ number_format($utilizationEntry->start_kilometer_reading, 2) }} <small class="text-secondary">KM</small></p>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="bg-dark p-3 rounded-3 border border-secondary border-opacity-25 h-100">
                                            <p class="small fw-bold text-primary text-uppercase tracking-widest mb-2">End KM</p>
                                            <p class="h4 fw-bold text-white mb-0">{{ number_format($utilizationEntry->end_kilometer_reading, 2) }} <small class="text-secondary">KM</small></p>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="bg-dark p-3 rounded-3 border border-secondary border-opacity-25 h-100">
                                            <p class="small fw-bold text-primary text-uppercase tracking-widest mb-2">Start Hours</p>
                                            <p class="h4 fw-bold text-white mb-0">{{ number_format($utilizationEntry->start_hour_reading, 2) }} <small class="text-secondary">HR</small></p>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="bg-dark p-3 rounded-3 border border-secondary border-opacity-25 h-100">
                                            <p class="small fw-bold text-primary text-uppercase tracking-widest mb-2">End Hours</p>
                                            <p class="h4 fw-bold text-white mb-0">{{ number_format($utilizationEntry->end_hour_reading, 2) }} <small class="text-secondary">HR</small></p>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="bg-dark p-3 rounded-3 border border-secondary border-opacity-25 h-100">
                                            <p class="small fw-bold text-primary text-uppercase tracking-widest mb-2">Total KM</p>
                                            <p class="h4 fw-bold text-white mb-0">{{ number_format($utilizationEntry->end_kilometer_reading - $utilizationEntry->start_kilometer_reading, 2) }} <small class="text-secondary">KM</small></p>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="bg-dark p-3 rounded-3 border border-secondary border-opacity-25 h-100">
                                            <p class="small fw-bold text-primary text-uppercase tracking-widest mb-2">Total Hours</p>
                                            <p class="h4 fw-bold text-white mb-0">{{ number_format($utilizationEntry->end_hour_reading - $utilizationEntry->start_hour_reading, 2) }} <small class="text-secondary">HR</small></p>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-4 pt-3 border-top border-secondary border-opacity-25">
                                    <p class="small fw-bold text-secondary text-uppercase tracking-widest mb-2">Fuel Order Reference</p>
                                    @if($utilizationEntry->fuel_order_id)
                                        <a href="{{ route('fuel-orders.show', $utilizationEntry->fuel_order_id) }}" class="btn btn-outline-primary btn-sm fw-bold tracking-widest">
                                            #{{ $utilizationEntry->fuel_order_id }}
                                        </a>
                                    @else
                                        <span class="small text-secondary fw-bold text-uppercase tracking-widest fst-italic">No reference provided</span>
                                    @endif
                                </div>

                                <div class="pt-3 border-top border-secondary border-opacity-25">
                                    <p class="small fw-bold text-secondary text-uppercase tracking-widest mb-2">Log Metadata</p>
                                    <div class="vstack gap-2">
                                        <div class="d-flex align-items-center small text-secondary fw-bold text-uppercase tracking-widest">
                                            <i class="bi bi-clock me-2"></i>
                                            Created: {{ $utilizationEntry->created_at->format('M d, Y @ H:i') }}
                                        </div>
                                        <div class="d-flex align-items-center small text-secondary fw-bold text-uppercase tracking-widest">
                                            <i class="bi bi-person me-2"></i>
                                            By {{ $utilizationEntry->creator->name ?? 'System' }}
                                        </div>
                                        
                                        @if($utilizationEntry->updated_at && $utilizationEntry->updated_at->ne($utilizationEntry->created_at) && $utilizationEntry->updated_by)
                                            <hr class="border-secondary border-opacity-25 my-1">
                                            <div class="d-flex align-items-center small text-primary fw-bold text-uppercase tracking-widest">
                                                <i class="bi bi-arrow-repeat me-2"></i>
                                                Updated: {{ $utilizationEntry->updated_at->format('M d, Y @ H:i') }}
                                            </div>
                                            <div class="d-flex align-items-center small text-primary fw-bold text-uppercase tracking-widest">
                                                <i class="bi bi-person me-2"></i>
                                                By {{ $utilizationEntry->updater->name ?? 'System' }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
