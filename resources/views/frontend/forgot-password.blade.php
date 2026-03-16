@extends('frontend.layouts.app')

@section('title', 'Forgot Password')

@section('content')
<div class="auth-container">
    <div class="card border-0 shadow-lg">
        <div class="card-body p-5">
            <div class="text-center mb-4">
                <h2 class="card-title mb-1">
                    <i class="bi bi-question-circle-fill" style="color: #3498db;"></i> Forgot Password
                </h2>
                <p class="text-muted">Enter your phone number to reset your password</p>
            </div>

            <form method="POST" action="{{ route('frontend.forgot-password') }}">
                @csrf

                <div class="mb-4">
                    <label for="phone" class="form-label">Phone Number</label>
                    <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                           id="phone" name="phone" placeholder="Enter your registered phone number" value="{{ old('phone') }}" required autofocus>
                    <small class="text-muted d-block mt-1">We'll send an OTP to verify your identity</small>
                    @error('phone')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary w-100 mb-3">
                    <i class="bi bi-send"></i> Send OTP to Phone
                </button>

                <div class="text-center">
                    <p class="text-muted">
                        Remember your password? 
                        <a href="{{ route('frontend.login') }}" class="text-decoration-none">Login here</a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
