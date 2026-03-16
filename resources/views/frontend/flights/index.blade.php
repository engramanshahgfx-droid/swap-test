@extends('frontend.layouts.app')

@section('title', 'Flights')

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h2 class="mb-0">
            <i class="bi bi-list"></i> All Available Flights
        </h2>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('frontend.flights.add') }}" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Add New Flight
        </a>
    </div>
</div>

<div class="row">
    @forelse($flights as $flight)
    <div class="col-md-6 mb-4">
        <div class="card border-0 shadow">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h5 class="card-title mb-1">
                            <i class="bi bi-airplane"></i> {{ $flight->flight_number }}
                        </h5>
                        <p class="text-muted mb-2">
                            {{ $flight->airline->name ?? 'Unknown' }} - {{ $flight->planeType->name ?? 'Unknown' }}
                        </p>
                    </div>
                    <span class="badge bg-{{ $flight->status === 'scheduled' ? 'info' : ($flight->status === 'completed' ? 'success' : 'danger') }}">
                        {{ ucfirst($flight->status) }}
                    </span>
                </div>

                <div class="mb-3">
                    <div class="row g-2 text-sm">
                        <div class="col-6">
                            <small class="text-muted d-block">FROM</small>
                            <strong>{{ $flight->departureAirport->code ?? 'N/A' }}</strong>
                            <small class="text-muted d-block">{{ $flight->departureAirport->name ?? '' }}</small>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">TO</small>
                            <strong>{{ $flight->arrivalAirport->code ?? 'N/A' }}</strong>
                            <small class="text-muted d-block">{{ $flight->arrivalAirport->name ?? '' }}</small>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="row g-2 mb-3 text-sm">
                    <div class="col-6">
                        <small class="text-muted d-block">Departure</small>
                        <strong>{{ $flight->departure_date->format('M d, Y') }}</strong>
                        <small class="text-muted d-block">{{ $flight->departure_time->format('H:i') }}</small>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Crew Assigned</small>
                        <strong>{{ $flight->assignedCrew()->count() }}</strong>
                        <small class="text-muted d-block">members</small>
                    </div>
                </div>

                @php
                    $userAssigned = $flight->assignedCrew()->where('user_id', Auth::id())->exists();
                @endphp

                <div class="d-grid gap-2">
                    @if($userAssigned)
                        <form method="POST" action="{{ route('frontend.flights.leave', $flight->id) }}" class="d-grid">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                <i class="bi bi-x-circle"></i> Leave Flight
                            </button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('frontend.flights.join', $flight->id) }}" class="d-grid">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="bi bi-check-circle"></i> Join Flight
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="alert alert-info text-center py-5">
            <i class="bi bi-info-circle" style="font-size: 2rem;"></i>
            <p class="mt-3 mb-0">No flights available yet. 
                <a href="{{ route('frontend.flights.add') }}">Create one</a>
            </p>
        </div>
    </div>
    @endforelse
</div>
@endsection
