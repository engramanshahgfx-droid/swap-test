@extends('frontend.layouts.app')

@section('title', 'Welcome - Crew Swap System')

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <!-- Hero Section -->
        <div class="text-center mb-5 mt-5">
            <h1 class="display-4 fw-bold mb-3" style="color: white;">
                <i class="bi bi-airplane" style="font-size: 3rem;"></i>
                <br>Welcome to Crew Swap System
            </h1>
            <p class="lead text-white mb-4">
                Manage your flights, add crews, and streamline crew scheduling
            </p>

            <div class="d-grid gap-3 d-sm-flex justify-content-center">
                <a href="{{ route('frontend.register') }}" class="btn btn-primary btn-lg px-5">
                    <i class="bi bi-person-plus-fill"></i> Sign Up
                </a>
                <a href="{{ route('frontend.login') }}" class="btn btn-outline-light btn-lg px-5">
                    <i class="bi bi-box-arrow-in-right"></i> Sign In
                </a>
            </div>
        </div>

        <!-- Features Section -->
        <div class="row mt-5 mb-5">
            <div class="col-md-4 mb-4">
                <div class="card border-0 shadow h-100 text-center">
                    <div class="card-body">
                        <div class="mb-3" style="font-size: 3rem; color: #3498db;">
                            <i class="bi bi-person-check-fill"></i>
                        </div>
                        <h5 class="card-title">Easy Registration</h5>
                        <p class="card-text text-muted">
                            Sign up quickly with OTP verification. Secure and simple.
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card border-0 shadow h-100 text-center">
                    <div class="card-body">
                        <div class="mb-3" style="font-size: 3rem; color: #27ae60;">
                            <i class="bi bi-shield-lock-fill"></i>
                        </div>
                        <h5 class="card-title">Secure Login</h5>
                        <p class="card-text text-muted">
                            Protected authentication with multi-factor OTP verification.
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card border-0 shadow h-100 text-center">
                    <div class="card-body">
                        <div class="mb-3" style="font-size: 3rem; color: #f39c12;">
                            <i class="bi bi-plus-circle-fill"></i>
                        </div>
                        <h5 class="card-title">Add Flights</h5>
                        <p class="card-text text-muted">
                            Create flights and assign crew members with ease.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Manager's Goals -->
        <div class="card border-0 shadow-lg mb-5" style="background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%); color: white;">
            <div class="card-body p-5">
                <h4 class="card-title mb-4">
                    <i class="bi bi-target"></i> Manager's Goals
                </h4>
                <div class="row">
                    <div class="col-md-4 mb-3 mb-md-0">
                        <div class="d-flex align-items-start">
                            <div class="rounded-circle bg-light text-dark d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; flex-shrink: 0;">
                                <strong>1</strong>
                            </div>
                            <div>
                                <h6 class="mb-2">Sign Up</h6>
                                <small>Complete user registration with all necessary details</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3 mb-md-0">
                        <div class="d-flex align-items-start">
                            <div class="rounded-circle bg-light text-dark d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; flex-shrink: 0;">
                                <strong>2</strong>
                            </div>
                            <div>
                                <h6 class="mb-2">Sign In</h6>
                                <small>Secure login with OTP verification</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-start">
                            <div class="rounded-circle bg-light text-dark d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; flex-shrink: 0;">
                                <strong>3</strong>
                            </div>
                            <div>
                                <h6 class="mb-2">Add Flights</h6>
                                <small>Create and manage flight schedules</small>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="bg-light my-4">

                <p class="mb-0">
                    <i class="bi bi-check-circle"></i> <strong>Backend & Mobile Integration Ready</strong>
                </p>
            </div>
        </div>

        <!-- Stats -->
        <div class="row mb-5">
            <div class="col-md-3 text-center">
                <h3 style="color: white;">1000+</h3>
                <p class="text-white-50">Active Users</p>
            </div>
            <div class="col-md-3 text-center">
                <h3 style="color: white;">500+</h3>
                <p class="text-white-50">Flights Managed</p>
            </div>
            <div class="col-md-3 text-center">
                <h3 style="color: white;">24/7</h3>
                <p class="text-white-50">Support</p>
            </div>
            <div class="col-md-3 text-center">
                <h3 style="color: white;">99.9%</h3>
                <p class="text-white-50">Uptime</p>
            </div>
        </div>
    </div>
</div>
@endsection
