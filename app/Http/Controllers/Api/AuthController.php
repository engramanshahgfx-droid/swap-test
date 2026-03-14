<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\RegisterRequest;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\VerifyOtpRequest;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'employee_id' => $request->employee_id,
            'full_name' => $request->full_name,
            'phone' => $request->phone,
            'email' => $request->email,
            'country_base' => $request->country_base,
            'airline_id' => $request->airline_id,
            'plane_type_id' => $request->plane_type_id,
            'position_id' => $request->position_id,
            'password' => Hash::make($request->password),
        ]);

        // Generate and send OTP
        $otp = $user->generateOtp();
        $this->smsService->sendOtp($user->phone, $otp);

        // Assign role based on position
        $role = match($request->position_id) {
            1 => 'flight_attendant',
            2 => 'purser',
            3 => 'crew_manager',
            4 => 'admin',
            default => 'flight_attendant',
        };
        $user->assignRole($role);

        // Create token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => __('auth.register_success'),
            'data' => [
                'user' => $user,
                'token' => $token,
                'requires_verification' => true,
            ],
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => __('auth.login_failed'),
            ], 401);
        }

        if (!$user->phone_verified_at) {
            // Send new OTP
            $otp = $user->generateOtp();
            $this->smsService->sendOtp($user->phone, $otp);

            return response()->json([
                'success' => false,
                'message' => __('auth.phone_not_verified'),
                'data' => [
                    'requires_verification' => true,
                    'user_id' => $user->id,
                ],
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => __('auth.login_success'),
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
        ]);
    }

    public function verifyOtp(VerifyOtpRequest $request)
    {
        $user = User::findOrFail($request->user_id);

        if ($user->verifyOtp($request->otp)) {
            $user->phone_verified_at = now();
            $user->otp_code = null;
            $user->otp_expires_at = null;
            $user->save();

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => __('auth.verify_otp_success'),
                'data' => [
                    'user' => $user,
                    'token' => $token,
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('auth.verify_otp_failed'),
        ], 400);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => __('auth.logout_success'),
        ]);
    }

    public function resendOtp(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::find($request->user_id);
        $otp = $user->generateOtp();
        $this->smsService->sendOtp($user->phone, $otp);

        return response()->json([
            'success' => true,
            'message' => __('auth.otp_sent'),
        ]);
    }

    /**
     * Simple registration for testing (no SMS required)
     */
    public function simpleRegister(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'full_name' => 'required|string|max:255',
        ]);

        try {
            // Generate unique employee_id from email
            $empId = strtoupper(substr(explode('@', $validated['email'])[0], 0, 8)) . rand(1000, 9999);

            $user = User::create([
                'employee_id' => $empId,
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'full_name' => $validated['full_name'],
                'phone' => '+1' . rand(1000000000, 9999999999), // Generate random phone
                'country_base' => 'United States',
                'status' => 'active',
                'phone_verified_at' => now(), // Skip SMS verification
                // These will be NULL, not required for testing
                // 'airline_id' => null,
                // 'plane_type_id' => null,
                // 'position_id' => null,
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Registration successful',
                'data' => [
                    'user' => $user,
                    'token' => $token,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Simple login for testing
     */
    public function simpleLogin(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password',
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
        ]);
    }
}
