@extends('layouts.auth')

@section('title', 'Admin Login - CrewSwap')

@section('styles')
    <style>
        .login-container {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
        }
        .login-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 48px 40px;
            width: 100%;
            max-width: 420px;
        }
        .login-header {
            text-align: center;
            margin-bottom: 32px;
        }
        .login-header h1 {
            font-size: 28px;
            font-weight: 800;
            color: #0f172a;
            margin: 0 0 8px;
        }
        .login-header p {
            color: #64748b;
            font-size: 14px;
            margin: 0;
        }
        .form-group {
            margin-bottom: 18px;
        }
        .form-group label {
            display: block;
            font-weight: 600;
            color: #334155;
            font-size: 14px;
            margin-bottom: 8px;
        }
        .form-group input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.2s;
            box-sizing: border-box;
        }
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .form-group input::placeholder {
            color: #94a3b8;
        }
        .form-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 24px 0;
            font-size: 13px;
        }
        .form-actions label {
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            margin: 0;
            font-weight: 500;
            color: #475569;
        }
        .form-actions input[type="checkbox"] {
            width: auto;
            margin: 0;
        }
        .form-actions a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }
        .form-actions a:hover {
            color: #764ba2;
        }
        .btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s;
            margin-top: 8px;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        .btn-login:active {
            transform: translateY(0);
        }
        .alert {
            padding: 12px 14px;
            border-radius: 8px;
            margin-bottom: 18px;
            font-size: 13px;
        }
        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        .alert ul {
            margin: 0;
            padding-left: 18px;
        }
        .alert li {
            margin: 4px 0;
        }
        .login-footer {
            text-align: center;
            margin-top: 24px;
            font-size: 13px;
            color: #64748b;
        }
        .login-footer a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        .login-footer a:hover {
            text-decoration: underline;
        }
    </style>
@endsection

@section('content')
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1>CrewSwap Admin</h1>
                <p>Sign in to your dashboard</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Login Failed:</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="admin@crewswap.com"
                        required
                        autofocus
                    />
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        required
                    />
                </div>

                <div class="form-actions">
                    <label>
                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }} />
                        Remember me
                    </label>
                </div>

                <button type="submit" class="btn-login">Sign In</button>
            </form>

            <!-- <div class="login-footer">
                <p style="margin: 0; color: #94a3b8;">Demo Credentials:</p>
                <p style="margin: 4px 0 0;"><strong>admin@crewswap.com</strong></p>
                <p style="margin: 4px 0;"><strong>password</strong></p>
            </div> -->
        </div>
    </div>
@endsection

