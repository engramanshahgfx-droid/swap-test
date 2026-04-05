@extends('frontend.layouts.app')

@section('title', 'Dashboard')

@section('content')
@php
    // Get user data from either Auth or Session
    $user = Auth::user();
    $userData = $user ? $user : session('verified_user_data');
    $fullName = $user ? $user->full_name : ($userData['full_name'] ?? 'User');
    $positionName = $user ? ($user->position->name ?? 'N/A') : ($userData['position'] ?? 'N/A');
    $airlineName = $user ? ($user->airline->name ?? 'N/A') : ($userData['airline'] ?? 'N/A');
    $totalFlights = $user ? \App\Models\Flight::count() : 0;
    $myFlightCount = $user ? $user->userTrips()->count() : 0;
@endphp

<div class="row">
    <!-- Welcome Card -->
    <div class="col-md-12 mb-4">
        <div class="card border-0 shadow-lg" style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); color: white;">
            <div class="card-body p-4">
                <h3 class="card-title mb-2">
                    <i class="bi bi-hand-thumbs-up"></i> Welcome, {{ $fullName }}!
                </h3>
                <p class="card-text mb-0">
                    <i class="bi bi-info-circle"></i> You are logged into the Crew Swap System
                </p>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="col-md-3 mb-4">
        <div class="card border-0 shadow text-center">
            <div class="card-body">
                <h5 class="text-muted mb-2">Total Flights</h5>
                <h2 style="color: #3498db;">{{ $totalFlights }}</h2>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-4">
        <div class="card border-0 shadow text-center">
            <div class="card-body">
                <h5 class="text-muted mb-2">My Flights</h5>
                <h2 style="color: #27ae60;">{{ $myFlightCount }}</h2>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-4">
        <div class="card border-0 shadow text-center">
            <div class="card-body">
                <h5 class="text-muted mb-2">Position</h5>
                <h2 style="color: #f39c12;">{{ $positionName }}</h2>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-4">
        <div class="card border-0 shadow text-center">
            <div class="card-body">
                <h5 class="text-muted mb-2">Airline</h5>
                <h2 style="color: #e74c3c;">{{ $airlineName }}</h2>
            </div>
        </div>
    </div>

    <!-- Manager's Goals -->
    <div class="col-md-12 mb-4">
        <div class="card border-0 shadow-lg">
            <div class="card-header" style="background: #2c3e50; color: white;">
                <h5 class="mb-0">
                    <i class="bi bi-target"></i> Manager's Goals for Backend & Mobile
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <span class="badge bg-primary mb-2">Goal 1</span>
                            <h6>Sign Up System</h6>
                            <p class="text-muted">Complete user registration with validation</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <span class="badge bg-success mb-2">Goal 2</span>
                            <h6>Sign In System</h6>
                            <p class="text-muted">Secure login with OTP verification</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <span class="badge bg-warning mb-2">Goal 3</span>
                            <h6>Add New Flight</h6>
                            <p class="text-muted">Create and manage flights with crew assignments</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="col-md-12">
        <div class="card border-0 shadow-lg">
            <div class="card-header" style="background: #2c3e50; color: white;">
                <h5 class="mb-0">
                    <i class="bi bi-lightning-charge"></i> Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('frontend.flights.add') }}" class="btn btn-primary w-100">
                            <i class="bi bi-plus-circle"></i> Add New Flight
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('frontend.flights.index') }}" class="btn btn-info w-100">
                            <i class="bi bi-list"></i> View All Flights
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('frontend.flights.my-flights') }}" class="btn btn-success w-100">
                            <i class="bi bi-bookmark"></i> My Flights
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('frontend.vacations') }}" class="btn btn-warning w-100">
                            <i class="bi bi-calendar-heart"></i> Vacation
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
