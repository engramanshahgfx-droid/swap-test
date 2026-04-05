@extends('frontend.layouts.app')

@section('title', 'Add New Flight')

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card border-0 shadow-lg">
            <div class="card-header" style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); color: white;">
                <h4 class="mb-0">
                    <i class="bi bi-plus-circle"></i> Add New Flight
                </h4>
            </div>
            <div class="card-body p-5">
                <form method="POST" action="{{ route('frontend.flights.store') }}">
                    @csrf

                    <div class="mb-4">
                        <label for="flight_number" class="form-label">Flight Number</label>
                        <input type="text" class="form-control @error('flight_number') is-invalid @enderror" 
                               id="flight_number" name="flight_number" value="{{ old('flight_number') }}" 
                               placeholder="e.g., AA123" required>
                        <small class="text-muted d-block mt-1">Must be unique</small>
                        @error('flight_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="airline_id" class="form-label">Airline</label>
                            <select class="form-select @error('airline_id') is-invalid @enderror" id="airline_id" name="airline_id" required>
                                <option value="">Select Airline</option>
                                @foreach($airlines as $airline)
                                <option value="{{ $airline->id }}" {{ old('airline_id') == $airline->id ? 'selected' : '' }}>
                                    {{ $airline->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('airline_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="plane_type_id" class="form-label">Plane Type</label>
                            <select class="form-select @error('plane_type_id') is-invalid @enderror" id="plane_type_id" name="plane_type_id" required>
                                <option value="">Select Plane Type</option>
                                @foreach($planeTypes as $type)
                                <option value="{{ $type->id }}" {{ old('plane_type_id') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('plane_type_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="departure_airport_id" class="form-label">Departure Airport</label>
                            <select class="form-select @error('departure_airport_id') is-invalid @enderror" 
                                    id="departure_airport_id" name="departure_airport_id" required>
                                <option value="">Select Airport</option>
                                @foreach($airports as $airport)
                                <option value="{{ $airport->id }}" {{ old('departure_airport_id') == $airport->id ? 'selected' : '' }}>
                                    {{ $airport->code }} - {{ $airport->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('departure_airport_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="arrival_airport_id" class="form-label">Arrival Airport</label>
                            <select class="form-select @error('arrival_airport_id') is-invalid @enderror" 
                                    id="arrival_airport_id" name="arrival_airport_id" required>
                                <option value="">Select Airport</option>
                                @foreach($airports as $airport)
                                <option value="{{ $airport->id }}" {{ old('arrival_airport_id') == $airport->id ? 'selected' : '' }}>
                                    {{ $airport->code }} - {{ $airport->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('arrival_airport_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="departure_date" class="form-label">Departure Date</label>
                            <input type="date" class="form-control @error('departure_date') is-invalid @enderror" 
                                   id="departure_date" name="departure_date" value="{{ old('departure_date') }}" required>
                            @error('departure_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="departure_time" class="form-label">Departure Time</label>
                            <input type="time" class="form-control @error('departure_time') is-invalid @enderror" 
                                   id="departure_time" name="departure_time" value="{{ old('departure_time') }}" required>
                            @error('departure_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="arrival_time" class="form-label">Arrival Time</label>
                            <input type="time" class="form-control @error('arrival_time') is-invalid @enderror" 
                                   id="arrival_time" name="arrival_time" value="{{ old('arrival_time') }}" required>
                            @error('arrival_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="status" class="form-label">Flight Status</label>
                        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                            <option value="">Select Status</option>
                            <option value="scheduled" {{ old('status') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                            <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                        @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-circle"></i> Create Flight
                        </button>
                        <a href="{{ route('frontend.flights.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
