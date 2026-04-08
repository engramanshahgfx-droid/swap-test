<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\RegisterRequest;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\VerifyOtpRequest;
use App\Models\User;
use App\Services\SmsService;
use App\Services\EmailOtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    protected $smsService;
    protected $emailOtpService;

    public function __construct(SmsService $smsService, EmailOtpService $emailOtpService)
    {
        $this->smsService = $smsService;
        $this->emailOtpService = $emailOtpService;
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
            'status' => 'inactive',
        ]);

        // Generate and send OTP (prefer email for mobile, fallback to SMS)
        $otp = $user->generateOtp();
        $otpDelivery = $this->deliverOtp($user, $otp, 'auto');

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

        $response = [
            'success' => true,
            'message' => __('auth.register_success'),
            'data' => [
                'user' => $user,
                'token' => $token,
                'requires_verification' => true,
                'otp_delivery' => $otpDelivery,
            ],
        ];

        if (!$otpDelivery['sent']) {
            $response['message'] = 'Registration completed, but OTP delivery failed. Use test_otp in debug mode or check email/SMS configuration.';
        }

        // For development/testing: include actual OTP
        if (config('app.debug')) {
            $response['data']['test_otp'] = $otp;
            $response['data']['otp_expires_in_minutes'] = 10;
        }

        return response()->json($response, 201);
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

        if ($user->status === 'inactive') {
            return response()->json([
                'success' => false,
                'message' => 'Your account is pending admin approval. Please wait for activation.',
            ], 403);
        }

        if ($user->status === 'blocked') {
            return response()->json([
                'success' => false,
                'message' => 'Your account has been blocked. Please contact support.',
            ], 403);
        }

        if (!$user->phone_verified_at) {
            // Send new OTP (prefer email for mobile, fallback to SMS)
            $otp = $user->generateOtp();
            $otpDelivery = $this->deliverOtp($user, $otp, 'auto');

            $response = [
                'success' => false,
                'message' => $otpDelivery['sent']
                    ? 'Account not verified yet. OTP sent via ' . $otpDelivery['channel'] . '.'
                    : 'Account not verified and OTP delivery failed. Use test_otp in debug mode or check email/SMS configuration.',
                'data' => [
                    'requires_verification' => true,
                    'user_id' => $user->id,
                    'otp_delivery' => $otpDelivery,
                ],
            ];

            // For development/testing: include actual OTP
            if (config('app.debug')) {
                $response['data']['test_otp'] = $otp;
                $response['data']['otp_expires_in_minutes'] = 10;
            }

            return response()->json($response, 403);
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

        $failureMessage = __('auth.verify_otp_failed');

        if (empty($user->otp_code) || empty($user->otp_expires_at)) {
            $failureMessage = 'No active OTP found. Please request a new OTP.';
        } elseif ($user->otp_expires_at->isPast()) {
            $failureMessage = 'OTP has expired. Please request a new OTP.';
        }

        return response()->json([
            'success' => false,
            'message' => $failureMessage,
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

    public function refreshToken(Request $request)
    {
        $user = $request->user();
        $currentToken = $user->currentAccessToken();

        if ($currentToken) {
            $currentToken->delete();
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Token refreshed successfully',
            'data' => [
                'token' => $token,
            ],
        ]);
    }

    public function resendOtp(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'channel' => 'nullable|in:auto,email,sms',
        ]);

        $user = User::find($request->user_id);
        $otp = $user->generateOtp();

        $channel = $request->input('channel', 'auto');
        $otpDelivery = $this->deliverOtp($user, $otp, $channel);

        if (!$otpDelivery['sent']) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to resend OTP via the requested channel',
                'data' => [
                    'user_id' => $user->id,
                    'channel' => $channel,
                    'otp_delivery' => $otpDelivery,
                ],
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => __('auth.otp_sent'),
            'data' => [
                'user_id' => $user->id,
                'channel' => $otpDelivery['channel'],
                'otp_delivery' => $otpDelivery,
            ],
        ]);
    }

    private function deliverOtp(User $user, string $otp, string $channel = 'auto'): array
    {
        $emailError = null;

        if (($channel === 'email' || $channel === 'auto') && !empty($user->email)) {
            $emailSent = $this->emailOtpService->sendOtp($user->email, $otp);

            if ($emailSent) {
                return [
                    'sent' => true,
                    'channel' => 'email',
                    'email' => $user->email,
                ];
            }

            $emailError = $this->emailOtpService->getLastError();
        }

        if (($channel === 'sms' || $channel === 'auto') && !empty($user->phone)) {
            $smsSent = (bool) $this->smsService->sendOtp($user->phone, $otp);

            if ($smsSent) {
                return [
                    'sent' => true,
                    'channel' => 'sms',
                    'phone' => $user->phone,
                    'email_error' => $emailError,
                ];
            }
        }

        return [
            'sent' => false,
            'channel' => null,
            'email' => $user->email,
            'phone' => $user->phone,
            'email_error' => $emailError,
        ];
    }

    /**
     * Simple registration for testing (OTP sent via email)
     */
    public function simpleRegister(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'nullable|string|max:50|unique:users,employee_id',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'full_name' => 'required|string|max:255',
        ]);

        try {
            // Generate unique employee_id from email
            $empId = !empty($validated['employee_id'])
                ? $validated['employee_id']
                : strtoupper(substr(explode('@', $validated['email'])[0], 0, 8)) . rand(1000, 9999);

            $user = User::create([
                'employee_id' => $empId,
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'full_name' => $validated['full_name'],
                'phone' => '+1' . rand(1000000000, 9999999999), // Generate random phone
                'country_base' => 'United States',
                'status' => 'inactive',
                'phone_verified_at' => null, // NOT verified yet - needs OTP
            ]);

            // Generate and send OTP via EMAIL
            $otp = $user->generateOtp();
            $otpSent = $this->emailOtpService->sendOtp($user->email, $otp);

            $token = $user->createToken('auth_token')->plainTextToken;

            $message = $otpSent
                ? 'Registration successful! OTP sent to ' . $user->email
                : 'Registration successful, but OTP email delivery failed. Use the test OTP below in local mode.';

            $response = [
                'success' => true,
                'message' => $message,
                'data' => [
                    'user' => $user,
                    'token' => $token,
                    'requires_verification' => true,
                    'otp_message' => $otpSent
                        ? 'Check your email for OTP code (expires in 10 minutes)'
                        : 'Email delivery failed in the current Resend configuration. Use test_otp in local mode or verify your Resend sender domain.',
                    'otp_delivery' => [
                        'sent' => $otpSent,
                        'channel' => 'email',
                    ],
                ],
            ];

            if (!$otpSent && $this->emailOtpService->getLastError()) {
                $response['data']['otp_delivery']['error'] = $this->emailOtpService->getLastError();
            }

            // For development/testing: include actual OTP
            if (config('app.debug')) {
                $response['data']['test_otp'] = $otp;
                $response['data']['otp_expires_in_minutes'] = 10;
            }

            return response()->json($response, 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Simple login for testing (requires OTP verification)
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

        // Check if email is verified via OTP
        if (!$user->phone_verified_at) {
            // Send new OTP if not yet verified
            $otp = $user->generateOtp();
            $otpSent = $this->emailOtpService->sendOtp($user->email, $otp);

            $message = $otpSent
                ? 'Please verify your email first. OTP sent to ' . $user->email
                : 'Please verify your email first. OTP email delivery failed, so use the test OTP below in local mode.';

            $response = [
                'success' => false,
                'message' => $message,
                'data' => [
                    'requires_verification' => true,
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'otp_delivery' => [
                        'sent' => $otpSent,
                        'channel' => 'email',
                    ],
                ],
            ];

            if (!$otpSent && $this->emailOtpService->getLastError()) {
                $response['data']['otp_delivery']['error'] = $this->emailOtpService->getLastError();
            }

            // For development/testing: include actual OTP
            if (config('app.debug')) {
                $response['data']['test_otp'] = $otp;
                $response['data']['otp_expires_in_minutes'] = 10;
            }

            return response()->json($response, 403);
        }

        // Email is verified - proceed with login
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => '✅ Login successful!',
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
        ]);
    }
}
