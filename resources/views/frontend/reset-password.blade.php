@extends('frontend.layouts.app')

@section('title', 'Reset Password')

@section('content')
<div class="auth-container">
    <div class="card border-0 shadow-lg">
        <div class="card-body p-5">
            <div class="text-center mb-4">
                <h2 class="card-title mb-1">
                    <i class="bi bi-arrow-repeat" style="color: #3498db;"></i> Reset Password
                </h2>
                <p class="text-muted">Enter your new password</p>
            </div>

            <form method="POST" action="{{ route('frontend.reset-password') }}">
                @csrf

                <div class="mb-3">
                    <label for="password" class="form-label">New Password</label>
                    <input type="password" class="form-control @error('password') is-invalid @enderror" 
                           id="password" name="password" placeholder="At least 4 characters" required>
                    <small class="text-muted d-block mt-1">Minimum 4 characters</small>
                    @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror" 
                           id="password_confirmation" name="password_confirmation" placeholder="Repeat your password" required>
                    <small class="text-muted d-block mt-1">Must match password above</small>
                    @error('password_confirmation')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary w-100 mb-3">
                    <i class="bi bi-check-circle"></i> Reset Password
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
