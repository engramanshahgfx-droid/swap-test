@extends('frontend.layouts.app')

@section('title', 'Create Account')

@section('content')
<div class="auth-container">
    <div class="card border-0 shadow-lg">
        <div class="card-body p-5">
            <div class="text-center mb-4">
                <h2 class="card-title mb-1">
                    <i class="bi bi-person-plus-fill" style="color: #3498db;"></i> Create Account
                </h2>
                <p class="text-muted">Join the Crew Swap System</p>
            </div>

            <form method="POST" action="{{ route('frontend.register') }}">
                @csrf

                <div class="mb-3">
                    <label for="employee_id" class="form-label">Employee ID</label>
                    <input type="text" class="form-control @error('employee_id') is-invalid @enderror" 
                           id="employee_id" name="employee_id" value="{{ old('employee_id') }}" required>
                    @error('employee_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="full_name" class="form-label">Full Name</label>
                    <input type="text" class="form-control @error('full_name') is-invalid @enderror" 
                           id="full_name" name="full_name" value="{{ old('full_name') }}" required>
                    @error('full_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                           id="email" name="email" value="{{ old('email') }}" required>
                    @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label">Phone Number</label>
                    <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                           id="phone" name="phone" value="{{ old('phone') }}" required>
                    @error('phone')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="country_base" class="form-label">Country Base</label>
                    <input type="text" class="form-control @error('country_base') is-invalid @enderror" 
                           id="country_base" name="country_base" value="{{ old('country_base') }}" required>
                    @error('country_base')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
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

                <div class="mb-3">
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

                <div class="mb-3">
                    <label for="position_id" class="form-label">Position</label>
                    <select class="form-select @error('position_id') is-invalid @enderror" id="position_id" name="position_id" required>
                        <option value="">Select Position</option>
                        @foreach($positions as $position)
                        <option value="{{ $position->id }}" {{ old('position_id') == $position->id ? 'selected' : '' }}>
                            {{ $position->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('position_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control @error('password') is-invalid @enderror" 
                           id="password" name="password" placeholder="Minimum 4 characters" required>
                    <small class="text-muted d-block mt-1">At least 4 characters</small>
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
                    <i class="bi bi-person-check"></i> Create Account
                </button>

                <div class="text-center">
                    <p class="text-muted">Already have an account? 
                        <a href="{{ route('frontend.login') }}" class="text-decoration-none">Login here</a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
