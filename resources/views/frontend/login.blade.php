@extends('frontend.layouts.app')

@section('title', 'Login')

@section('content')
<div class="auth-container">
    <div class="card border-0 shadow-lg">
        <div class="card-body p-5">
            <div class="text-center mb-4">
                <h2 class="card-title mb-1">
                    <i class="bi bi-lock-fill" style="color: #3498db;"></i> Login
                </h2>
                <p class="text-muted">Welcome back to Crew Swap System</p>
            </div>

            <form method="POST" action="{{ route('frontend.login') }}">
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                           id="email" name="email" value="{{ old('email') }}" required autofocus>
                    @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control @error('password') is-invalid @enderror" 
                           id="password" name="password" required>
                    @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                    <label class="form-check-label" for="remember">
                        Remember me
                    </label>
                </div>

                <button type="submit" class="btn btn-primary w-100 mb-3">
                    <i class="bi bi-box-arrow-in-right"></i> Login
                </button>

                <div class="text-center">
                    <p class="text-muted mb-2">
                        <a href="{{ route('frontend.forgot-password') }}" class="text-decoration-none">
                            Forgot your password?
                        </a>
                    </p>
                    <p class="text-muted">
                        Don't have an account? 
                        <a href="{{ route('frontend.register') }}" class="text-decoration-none">Create one</a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
