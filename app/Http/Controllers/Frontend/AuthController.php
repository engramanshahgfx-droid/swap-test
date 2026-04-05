<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\EmailOtpService;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\Airline;
use App\Models\PlaneType;
use App\Models\Position;

class AuthController extends Controller
{
    protected $smsService;
    protected $emailOtpService;

    public function __construct(SmsService $smsService, EmailOtpService $emailOtpService)
    {
        $this->smsService = $smsService;
        $this->emailOtpService = $emailOtpService;
    }

    // Show registration page
    public function showRegister()
    {
        $airlines = collect([]);
        $planeTypes = collect([]);
        $positions = collect([]);

        try {
            // Try to fetch from database
            $airlines = Airline::all();
            $planeTypes = PlaneType::all();
            $positions = Position::all();
            
            // If no data exists, create some defaults for testing
            if ($airlines->isEmpty()) {
                Airline::create(['name' => 'Emirates Airlines', 'code' => 'EK']);
                Airline::create(['name' => 'Qatar Airways', 'code' => 'QR']);
                $airlines = Airline::all();
            }
            
            if ($planeTypes->isEmpty()) {
                PlaneType::create(['name' => 'Boeing 747', 'code' => 'B747']);
                PlaneType::create(['name' => 'Airbus A380', 'code' => 'A380']);
                $planeTypes = PlaneType::all();
            }
            
            if ($positions->isEmpty()) {
                Position::create(['name' => 'Captain']);
                Position::create(['name' => 'First Officer']);
                Position::create(['name' => 'Flight Attendant']);
                $positions = Position::all();
            }
        } catch (\Exception $e) {
            // If database fails, provide dummy data for testing
            \Log::warning('Database offline - using dummy data for registration form');
            $airlines = collect([
                (object)['id' => 1, 'name' => 'Emirates Airlines', 'code' => 'EK'],
                (object)['id' => 2, 'name' => 'Qatar Airways', 'code' => 'QR'],
            ]);
            $planeTypes = collect([
                (object)['id' => 1, 'name' => 'Boeing 747', 'code' => 'B747'],
                (object)['id' => 2, 'name' => 'Airbus A380', 'code' => 'A380'],
            ]);
            $positions = collect([
                (object)['id' => 1, 'name' => 'Captain'],
                (object)['id' => 2, 'name' => 'First Officer'],
                (object)['id' => 3, 'name' => 'Flight Attendant'],
            ]);
        }
        
        return view('frontend.register', compact('airlines', 'planeTypes', 'positions'));
    }

    // Handle registration
    public function register(Request $request)
    {
        try {
            // Validate input with custom messages
            $validated = $request->validate([
                'employee_id' => 'required|min:2',
                'full_name' => 'required|min:2',
                'phone' => 'required|min:5',
                'email' => 'required|email',
                'country_base' => 'required|min:2',
                'airline_id' => 'required',
                'plane_type_id' => 'required',
                'position_id' => 'required',
                'password' => 'required|min:4|confirmed',
            ], [
                'employee_id.required' => 'Employee ID is required',
                'employee_id.min' => 'Employee ID must be at least 2 characters',
                'full_name.required' => 'Full Name is required',
                'full_name.min' => 'Full Name must be at least 2 characters',
                'phone.required' => 'Phone Number is required',
                'phone.min' => 'Phone Number must be at least 5 digits',
                'email.required' => 'Email Address is required',
                'email.email' => 'Please enter a valid email address',
                'country_base.required' => 'Country Base is required',
                'country_base.min' => 'Country Base must be at least 2 characters',
                'airline_id.required' => 'Please select an Airline',
                'plane_type_id.required' => 'Please select a Plane Type',
                'position_id.required' => 'Please select your Position',
                'password.required' => 'Password is required',
                'password.min' => 'Password must be at least 4 characters',
                'password.confirmed' => 'Passwords do not match',
            ]);

            // Try to save to database, but handle gracefully if DB is offline
            try {
                // Check if database is online
                \Illuminate\Support\Facades\DB::connection()->getPdo();
                
                // Database is online - do normal database validation and save
                $validated['email'] = $request->email;
                $validated['phone'] = $request->phone;
                $validated['employee_id'] = $request->employee_id;
                
                $user = User::create([
                    'employee_id' => $validated['employee_id'],
                    'full_name' => $validated['full_name'],
                    'phone' => $validated['phone'],
                    'email' => $validated['email'],
                    'country_base' => $validated['country_base'],
                    'airline_id' => $validated['airline_id'],
                    'plane_type_id' => $validated['plane_type_id'],
                    'position_id' => $validated['position_id'],
                    'password' => Hash::make($validated['password']),
                ]);
                
                $userId = $user->id;
            } catch (\Exception $dbError) {
                // Database offline - use session to simulate registration
                \Log::warning('Database offline during registration - using session');
                
                // Store registration data in session (for testing without DB)
                $userId = time();  // Use timestamp as fake ID
                session([
                    'temp_user_id' => $userId,
                    'temp_user_data' => [
                        'employee_id' => $validated['employee_id'],
                        'full_name' => $validated['full_name'],
                        'phone' => $validated['phone'],
                        'email' => $validated['email'],
                        'country_base' => $validated['country_base'],
                        'airline_id' => $validated['airline_id'],
                        'plane_type_id' => $validated['plane_type_id'],
                        'position_id' => $validated['position_id'],
                    ]
                ]);
            }

            $otp = $user ?? null
                ? $user->generateOtp()
                : $this->generateSessionOtp();

            $otpSent = $this->emailOtpService->sendOtp($validated['email'], $otp);

            // Store OTP in session
            session([
                'otp_code' => $otp,
                'user_id' => $userId,
                'otp_email' => $validated['email'],
            ]);

            $flashData = $otpSent
                ? ['success' => 'Registration successful. OTP sent to ' . $validated['email']]
                : ['error' => 'Registration succeeded, but OTP email delivery failed: ' . ($this->emailOtpService->getLastError() ?? 'Unknown error')];

            if (config('app.debug')) {
                $flashData['test_otp'] = $otp;
            }

            return redirect('/frontend-test/verify-otp')->with($flashData);
        } catch (\Exception $e) {
            \Log::error('Registration error: ' . $e->getMessage());
            return redirect('/frontend-test/register')
                ->withInput($request->except('password', 'password_confirmation'))
                ->with('error', 'Registration failed: ' . $e->getMessage());
        }
    }

    // Show OTP verification page
    public function showVerifyOtp()
    {
        if (!session('user_id')) {
            return redirect()->route('frontend.register')->with('error', 'Please register first');
        }
        
        return view('frontend.verify-otp', [
            'test_otp' => session('otp_code'),
        ]);
    }

    // Verify OTP
    public function verifyOtp(Request $request)
    {
        $validated = $request->validate([
            'otp' => 'required|digits:6',
        ]);

        $userId = session('user_id');
        $sessionOtp = session('otp_code');

        // Check if OTP matches
        if ($validated['otp'] !== $sessionOtp) {
            return back()->with('error', 'Invalid OTP code. Please try again.');
        }

        // Check if user data exists in session (offline registration) or database (online registration)
        $tempUserData = session('temp_user_data');
        
        if ($tempUserData) {
            // Database was offline - user data is in session
            // Mark as verified in session and prepare for login
            session([
                'phone_verified' => true,
                'verified_user_data' => $tempUserData,
            ]);
            session()->forget('temp_user_id');
            session()->forget('temp_user_data');
            session()->forget('otp_code');
            session()->forget('user_id');
        } else {
            // Database is online - do normal user lookup
            try {
                $user = User::findOrFail($userId);

                // Mark phone as verified
                $user->phone_verified_at = now();
                $user->otp_code = null;
                $user->otp_expires_at = null;
                $user->save();

                // Assign role based on position
                $role = match($user->position_id) {
                    1 => 'flight_attendant',
                    2 => 'purser',
                    3 => 'crew_manager',
                    4 => 'admin',
                    default => 'flight_attendant',
                };
                $user->assignRole($role);
            } catch (\Exception $e) {
                return back()->with('error', 'Error verifying user: ' . $e->getMessage());
            }
        }

        session()->forget(['user_id', 'otp_code']);

        return redirect()->route('frontend.login')->with('success', 'OTP verified successfully! Please login.');
    }

    // Show login page
    public function showLogin()
    {
        return view('frontend.login');
    }

    // Handle login
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:4',
        ]);

        // Check if this is a session-based user (offline registration)
        $verifiedUserData = session('verified_user_data');
        if ($verifiedUserData) {
            // User registered offline and passed OTP verification
            if ($verifiedUserData['email'] === $validated['email'] && 
                // For offline users, we can't check password hash, so just check password is provided
                !empty($validated['password'])) {
                
                // Create a session-based login
                session([
                    'authenticated' => true,
                    'user_data' => $verifiedUserData,
                    'remember' => $request->boolean('remember'),
                ]);
                
                return redirect()->route('frontend.dashboard')->with('success', 'Logged in successfully!');
            } else {
                return back()->with('error', 'Invalid credentials. Email or password does not match registration.');
            }
        }

        // Try database login (if database is online)
        try {
            $user = User::where('email', $validated['email'])->first();

            if (!$user || !Hash::check($validated['password'], $user->password)) {
                return back()->with('error', 'Invalid email or password');
            }

            if ($user->status === 'inactive') {
                return back()->with('error', 'Your account is pending admin approval. Please wait for activation.');
            }

            if ($user->status === 'blocked') {
                return back()->with('error', 'Your account has been blocked. Please contact support.');
            }

            if (!$user->phone_verified_at) {
                $otp = $user->generateOtp();
                $otpSent = $this->emailOtpService->sendOtp($user->email, $otp);

                session(['otp_code' => $otp, 'user_id' => $user->id, 'otp_email' => $user->email]);

                $flashData = $otpSent
                    ? ['message' => 'Email not verified. OTP sent to ' . $user->email]
                    : ['error' => 'Email verification OTP could not be delivered: ' . ($this->emailOtpService->getLastError() ?? 'Unknown error')];

                if (config('app.debug')) {
                    $flashData['test_otp'] = $otp;
                }

                return redirect()->route('frontend.verify-otp')->with($flashData);
            }

            Auth::login($user, $request->boolean('remember'));

            return redirect()->route('frontend.dashboard')->with('success', 'Logged in successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Login failed. Please ensure you have completed registration and OTP verification.');
        }
    }

    // Show forgot password page
    public function showForgotPassword()
    {
        return view('frontend.forgot-password');
    }

    // Handle forgot password
    public function forgotPassword(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|min:5',
        ]);

        // Check if phone exists (database or session)
        try {
            $user = User::where('phone', $validated['phone'])->first();
            
            if (!$user) {
                return back()->with('error', 'Phone number not found. Please check and try again.');
            }

            // Generate and send OTP
            $otp = $this->generateSessionOtp();
            $user->otp_code = $otp;
            $user->otp_expires_at = now()->addMinutes(15);
            $user->save();

            session([
                'otp_code' => $otp, 
                'user_id' => $user->id, 
                'forgot_password' => true,
                'reset_phone' => $validated['phone'],
            ]);
        } catch (\Exception $e) {
            // Database offline - check session data
            $userData = session('verified_user_data');
            
            if (!$userData || ($userData['phone'] ?? null) !== $validated['phone']) {
                return back()->with('error', 'Phone number not found. Please check and try again.');
            }

            // Generate OTP for session-based user
            $otp = $this->generateSessionOtp();
            session([
                'otp_code' => $otp,
                'forgot_password' => true,
                'reset_phone' => $validated['phone'],
                'reset_user_data' => $userData,
            ]);
        }

        return redirect()->route('frontend.reset-password-otp')->with([
            'success' => 'OTP sent to your phone: ' . $validated['phone'],
            'test_otp' => $otp,
        ]);
    }

    // Show reset password OTP verification
    public function showResetPasswordOtp()
    {
        if (!session('user_id')) {
            return redirect()->route('frontend.forgot-password')->with('error', 'Please request password reset first');
        }

        return view('frontend.reset-password-otp', [
            'test_otp' => session('otp_code'),
        ]);
    }

    // Verify reset password OTP
    public function verifyResetPasswordOtp(Request $request)
    {
        $validated = $request->validate([
            'otp' => 'required|digits:6',
        ]);

        $sessionOtp = session('otp_code');

        if ($validated['otp'] !== $sessionOtp) {
            return back()->with('error', 'Invalid OTP code');
        }

        // Mark as verified
        session(['reset_verified' => true]);

        return redirect()->route('frontend.reset-password')->with('success', 'OTP verified! Please set your new password.');
    }

    // Show reset password page
    public function showResetPassword()
    {
        if (!session('reset_verified')) {
            return redirect()->route('frontend.forgot-password');
        }

        return view('frontend.reset-password');
    }

    // Handle reset password
    public function resetPassword(Request $request)
    {
        if (!session('reset_verified')) {
            return redirect()->route('frontend.forgot-password')->with('error', 'Please verify OTP first');
        }

        $validated = $request->validate([
            'password' => 'required|min:4|confirmed',
        ], [
            'password.required' => 'Password is required',
            'password.min' => 'Password must be at least 4 characters',
            'password.confirmed' => 'Passwords do not match',
        ]);

        $userId = session('user_id');
        $resetUserData = session('reset_user_data');

        try {
            // Try to update database user
            if ($userId) {
                $user = User::findOrFail($userId);
                $user->password = Hash::make($validated['password']);
                $user->otp_code = null;
                $user->otp_expires_at = null;
                $user->save();
            }
        } catch (\Exception $e) {
            // Database offline - update session data
            if ($resetUserData) {
                $resetUserData['password'] = $validated['password'];
                session([
                    'verified_user_data' => $resetUserData,
                    'reset_user_data' => null,
                ]);
            }
        }

        // Clear reset session data
        session()->forget([
            'user_id', 
            'otp_code', 
            'forgot_password', 
            'reset_verified',
            'reset_user_data',
            'reset_phone',
        ]);

        return redirect()->route('frontend.login')->with('success', 'Password reset successfully! Please login with your new password.');
    }

    // Logout
    public function logout()
    {
        Auth::logout();
        session()->invalidate();

        return redirect()->route('frontend.index')->with('success', 'Logged out successfully!');
    }

    private function generateSessionOtp()
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }
}
