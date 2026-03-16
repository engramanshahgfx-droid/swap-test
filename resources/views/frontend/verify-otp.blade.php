@extends('frontend.layouts.app')

@section('title', 'Verify OTP')

@section('content')
<div class="auth-container">
    <div class="card border-0 shadow-lg">
        <div class="card-body p-5">
            <div class="text-center mb-4">
                <h2 class="card-title mb-1">
                    <i class="bi bi-shield-check" style="color: #3498db;"></i> Verify OTP
                </h2>
                <p class="text-muted">Enter the OTP sent to your phone</p>
            </div>

            <!-- Test OTP Display -->
            @if($test_otp)
            <div class="test-otp-box">
                <strong><i class="bi bi-exclamation-triangle-fill"></i> Testing Mode:</strong><br>
                Use this OTP to verify: <code style="font-size: 1.1rem; font-weight: bold; color: #f39c12;">{{ $test_otp }}</code>
            </div>
            @endif

            <form method="POST" action="{{ route('frontend.verify-otp') }}">
                @csrf

                <div class="mb-4">
                    <label for="otp" class="form-label">Enter 6-Digit OTP</label>
                    <input type="text" class="form-control text-center @error('otp') is-invalid @enderror" 
                           id="otp" name="otp" placeholder="000000" maxlength="6" 
                           pattern="[0-9]{6}" required autofocus
                           style="font-size: 1.5rem; letter-spacing: 10px;">
                    <small class="text-muted d-block mt-2">
                        <i class="bi bi-info-circle"></i> Enter the 6-digit code from your SMS
                    </small>
                    @error('otp')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary w-100 mb-3">
                    <i class="bi bi-check-circle"></i> Verify OTP
                </button>

                <div class="text-center">
                    <p class="text-muted small mb-0">
                        Didn't receive OTP? Check your phone or wait a moment
                    </p>
                </div>
            </form>
        </div>
    </div>

    <div class="text-center mt-4">
        <a href="{{ route('frontend.register') }}" class="btn btn-outline-secondary">
            Back to Registration
        </a>
    </div>
</div>

@section('extra_js')
<script>
    // Auto-format OTP input
    document.getElementById('otp').addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);
    });
</script>
@endsection
@endsection
