@extends('frontend.layouts.app')

@section('title', 'My Flights')

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h2 class="mb-0">
            <i class="bi bi-bookmark"></i> My Assigned Flights
        </h2>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('frontend.flights.index') }}" class="btn btn-info">
            <i class="bi bi-list"></i> Browse Flights
        </a>
    </div>
</div>

<div class="row">
    @forelse($userFlights as $userFlight)
    <div class="col-md-6 mb-4">
        <div class="card border-0 shadow">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h5 class="card-title mb-1">
                            <i class="bi bi-airplane"></i> {{ $userFlight->flight->flight_number }}
                        </h5>
                        <p class="text-muted mb-2">
                            {{ $userFlight->flight->airline->name ?? 'Unknown' }} - {{ $userFlight->flight->planeType->name ?? 'Unknown' }}
                        </p>
                    </div>
                    <div>
                        <span class="badge bg-{{ $userFlight->flight->status === 'scheduled' ? 'info' : ($userFlight->flight->status === 'completed' ? 'success' : 'danger') }}">
                            {{ ucfirst($userFlight->flight->status) }}
                        </span>
                        <span class="badge bg-secondary ms-2">
                            {{ ucfirst($userFlight->role) }}
                        </span>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="row g-2 text-sm">
                        <div class="col-6">
                            <small class="text-muted d-block">FROM</small>
                            <strong>{{ $userFlight->flight->departureAirport->code ?? 'N/A' }}</strong>
                            <small class="text-muted d-block">{{ $userFlight->flight->departureAirport->name ?? '' }}</small>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">TO</small>
                            <strong>{{ $userFlight->flight->arrivalAirport->code ?? 'N/A' }}</strong>
                            <small class="text-muted d-block">{{ $userFlight->flight->arrivalAirport->name ?? '' }}</small>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="row g-2 mb-3 text-sm">
                    <div class="col-6">
                        <small class="text-muted d-block">Departure</small>
                        <strong>{{ $userFlight->flight->departure_date->format('M d, Y') }}</strong>
                        <small class="text-muted d-block">{{ $userFlight->flight->departure_time->format('H:i') }}</small>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Assigned As</small>
                        <strong>{{ ucfirst($userFlight->role) }}</strong>
                        <small class="text-muted d-block">{{ ucfirst($userFlight->status) }}</small>
                    </div>
                </div>

                <div class="d-grid">
                    <form method="POST" action="{{ route('frontend.flights.leave', $userFlight->flight->id) }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger btn-sm">
                            <i class="bi bi-x-circle"></i> Leave This Flight
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="alert alert-info text-center py-5">
            <i class="bi bi-info-circle" style="font-size: 2rem;"></i>
            <p class="mt-3 mb-0">You haven't been assigned to any flights yet. 
                <a href="{{ route('frontend.flights.index') }}">Browse available flights</a>
            </p>
        </div>
    </div>
    @endforelse
</div>
@endsection
