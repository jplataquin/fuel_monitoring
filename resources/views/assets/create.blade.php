<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-bold text-light mb-0">
            {{ __('Register New Asset') }}
        </h2>
    </x-slot>

    <div class="container-xl py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card bg-dark border-secondary border-opacity-25 shadow-lg">
                    <div class="card-body p-4 p-md-5">
                        <form method="POST" action="{{ route('assets.store') }}">
                            @csrf

                            <div class="row g-4">
                                <div class="col-12">
                                    <label for="fleet_no" class="form-label text-secondary text-uppercase small fw-bold tracking-widest ps-1 mb-2">
                                        {{ __('Fleet Number') }}
                                    </label>
                                    <input id="fleet_no" name="fleet_no" type="text" 
                                        class="form-control bg-dark bg-opacity-25 border-secondary border-opacity-50 text-light focus-ring focus-ring-primary" 
                                        value="{{ old('fleet_no') }}" required autofocus placeholder="e.g. DT-001">
                                    @if($errors->has('fleet_no'))
                                        <div class="text-danger small fw-bold mt-2 ps-1">{{ $errors->first('fleet_no') }}</div>
                                    @endif
                                </div>

                                <div class="col-md-6">
                                    <label for="asset_type_id" class="form-label text-secondary text-uppercase small fw-bold tracking-widest ps-1 mb-2">
                                        {{ __('Equipment Category') }}
                                    </label>
                                    <select id="asset_type_id" name="asset_type_id" class="form-select bg-dark bg-opacity-25 border-secondary border-opacity-50 text-light focus-ring focus-ring-primary" required>
                                        <option value="">Select Category</option>
                                        @foreach($assetTypes as $type)
                                            <option value="{{ $type->id }}" {{ old('asset_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('asset_type_id'))
                                        <div class="text-danger small fw-bold mt-2 ps-1">{{ $errors->first('asset_type_id') }}</div>
                                    @endif
                                </div>

                                <div class="col-md-6">
                                    <label for="plate_no" class="form-label text-secondary text-uppercase small fw-bold tracking-widest ps-1 mb-2">
                                        {{ __('Plate Number') }}
                                    </label>
                                    <input id="plate_no" name="plate_no" type="text" 
                                        class="form-control bg-dark bg-opacity-25 border-secondary border-opacity-50 text-light font-monospace focus-ring focus-ring-primary" 
                                        value="{{ old('plate_no') }}" placeholder="Optional">
                                    @if($errors->has('plate_no'))
                                        <div class="text-danger small fw-bold mt-2 ps-1">{{ $errors->first('plate_no') }}</div>
                                    @endif
                                </div>

                                <div class="col-md-6">
                                    <label for="fuel_type" class="form-label text-secondary text-uppercase small fw-bold tracking-widest ps-1 mb-2">
                                        {{ __('Fuel Type') }}
                                    </label>
                                    <select id="fuel_type" name="fuel_type" class="form-select bg-dark bg-opacity-25 border-secondary border-opacity-50 text-light focus-ring focus-ring-primary" required>
                                        <option value="Diesel" {{ old('fuel_type') == 'Diesel' ? 'selected' : '' }}>Diesel</option>
                                        <option value="Gasoline" {{ old('fuel_type') == 'Gasoline' ? 'selected' : '' }}>Gasoline</option>
                                    </select>
                                    @if($errors->has('fuel_type'))
                                        <div class="text-danger small fw-bold mt-2 ps-1">{{ $errors->first('fuel_type') }}</div>
                                    @endif
                                </div>

                                <div class="col-md-6">
                                    <label for="tank_capacity" class="form-label text-secondary text-uppercase small fw-bold tracking-widest ps-1 mb-2">
                                        {{ __('Tank Capacity (Liters)') }}
                                    </label>
                                    <input id="tank_capacity" name="tank_capacity" type="number" step="0.01" 
                                        class="form-control bg-dark bg-opacity-25 border-secondary border-opacity-50 text-light font-monospace focus-ring focus-ring-primary" 
                                        value="{{ old('tank_capacity') }}" required placeholder="0.00">
                                    @if($errors->has('tank_capacity'))
                                        <div class="text-danger small fw-bold mt-2 ps-1">{{ $errors->first('tank_capacity') }}</div>
                                    @endif
                                </div>

                                <div class="col-md-6">
                                    <label for="last_kilometer_reading" class="form-label text-secondary text-uppercase small fw-bold tracking-widest ps-1 mb-2">
                                        {{ __('Last Odo (KM)') }}
                                    </label>
                                    <input id="last_kilometer_reading" name="last_kilometer_reading" type="number" step="0.01" 
                                        class="form-control bg-dark bg-opacity-25 border-secondary border-opacity-50 text-light font-monospace focus-ring focus-ring-primary" 
                                        value="{{ old('last_kilometer_reading') }}" required placeholder="0.00">
                                    @if($errors->has('last_kilometer_reading'))
                                        <div class="text-danger small fw-bold mt-2 ps-1">{{ $errors->first('last_kilometer_reading') }}</div>
                                    @endif
                                </div>

                                <div class="col-md-6">
                                    <label for="last_engine_hours" class="form-label text-secondary text-uppercase small fw-bold tracking-widest ps-1 mb-2">
                                        {{ __('Last Engine (HR)') }}
                                    </label>
                                    <input id="last_engine_hours" name="last_engine_hours" type="number" step="0.01" 
                                        class="form-control bg-dark bg-opacity-25 border-secondary border-opacity-50 text-light font-monospace focus-ring focus-ring-primary" 
                                        value="{{ old('last_engine_hours') }}" required placeholder="0.00">
                                    @if($errors->has('last_engine_hours'))
                                        <div class="text-danger small fw-bold mt-2 ps-1">{{ $errors->first('last_engine_hours') }}</div>
                                    @endif
                                </div>

                                <div class="col-md-6">
                                    <label for="last_time" class="form-label text-secondary text-uppercase small fw-bold tracking-widest ps-1 mb-2">
                                        {{ __('Last Time') }}
                                    </label>
                                    <input id="last_time" name="last_time" type="time" 
                                        class="form-control bg-dark bg-opacity-25 border-secondary border-opacity-50 text-light font-monospace focus-ring focus-ring-primary" 
                                        value="{{ old('last_time') }}">
                                    @if($errors->has('last_time'))
                                        <div class="text-danger small fw-bold mt-2 ps-1">{{ $errors->first('last_time') }}</div>
                                    @endif
                                </div>

                                <div class="col-md-6">
                                    <label for="last_date" class="form-label text-secondary text-uppercase small fw-bold tracking-widest ps-1 mb-2">
                                        {{ __('Last Date') }}
                                    </label>
                                    <input id="last_date" name="last_date" type="date" 
                                        class="form-control bg-dark bg-opacity-25 border-secondary border-opacity-50 text-light font-monospace focus-ring focus-ring-primary" 
                                        value="{{ old('last_date') }}">
                                    @if($errors->has('last_date'))
                                        <div class="text-danger small fw-bold mt-2 ps-1">{{ $errors->first('last_date') }}</div>
                                    @endif
                                </div>

                                <div class="col-12 mt-5">
                                    <div class="pt-4 border-top border-secondary border-opacity-25">
                                        <h4 class="small fw-bold text-primary text-uppercase tracking-widest mb-4">Consumption Factors</h4>
                                        <div class="row g-4">
                                            <div class="col-md-6">
                                                <label for="fuel_factor_km" class="form-label text-secondary text-uppercase small fw-bold tracking-widest ps-1 mb-2">
                                                    {{ __('Kilometers / Liter') }}
                                                </label>
                                                <input id="fuel_factor_km" name="fuel_factor_km" type="number" step="0.01" 
                                                    class="form-control bg-dark bg-opacity-25 border-secondary border-opacity-50 text-light font-monospace focus-ring focus-ring-primary" 
                                                    value="{{ old('fuel_factor_km') }}" placeholder="0.00">
                                                @if($errors->has('fuel_factor_km'))
                                                    <div class="text-danger small fw-bold mt-2 ps-1">{{ $errors->first('fuel_factor_km') }}</div>
                                                @endif
                                            </div>

                                            <div class="col-md-6">
                                                <label for="fuel_factor_hr" class="form-label text-secondary text-uppercase small fw-bold tracking-widest ps-1 mb-2">
                                                    {{ __('Liters / Hour') }}
                                                </label>
                                                <input id="fuel_factor_hr" name="fuel_factor_hr" type="number" step="0.01" 
                                                    class="form-control bg-dark bg-opacity-25 border-secondary border-opacity-50 text-light font-monospace focus-ring focus-ring-primary" 
                                                    value="{{ old('fuel_factor_hr') }}" placeholder="0.00">
                                                @if($errors->has('fuel_factor_hr'))
                                                    <div class="text-danger small fw-bold mt-2 ps-1">{{ $errors->first('fuel_factor_hr') }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex align-items-center justify-content-end pt-4 mt-5 border-top border-secondary border-opacity-25 gap-3">
                                <a href="{{ route('assets.index') }}" class="btn btn-link text-secondary text-uppercase small fw-bold tracking-widest text-decoration-none px-4">
                                    Cancel
                                </a>
                                <button type="submit" class="btn btn-primary rounded-pill px-5 py-3 fw-bold text-uppercase tracking-widest shadow-sm">
                                    {{ __('Create') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
