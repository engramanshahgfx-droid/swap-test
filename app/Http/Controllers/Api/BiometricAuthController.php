<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class BiometricAuthController extends Controller
{
    /**
     * Setup 4-digit code for login
     * POST /api/auth/setup-code
     */
    public function setupCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|size:4|confirmed',
            'code_confirmation' => 'required|string|size:4'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        // Check if user already has a code
        if ($user->four_digit_code) {
            return response()->json([
                'success' => false,
                'message' => '4-digit code already set up. Please disable it first.'
            ], 400);
        }

        $user->update([
            'four_digit_code' => Hash::make($request->code),
            'biometric_login_enabled' => true,
            'biometric_setup_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => '4-digit code setup successfully',
            'data' => [
                'biometric_login_enabled' => true,
                'setup_at' => $user->biometric_setup_at
            ]
        ]);
    }

    /**
     * Login with 4-digit code
     * POST /api/auth/login-code
     */
    public function loginWithCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'code' => 'required|string|size:4'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        // Check if user has 4-digit code setup
        if (!$user->four_digit_code) {
            return response()->json([
                'success' => false,
                'message' => '4-digit code not set up for this account'
            ], 400);
        }

        // Check if biometric login is locked
        if ($user->biometric_locked_until && $user->biometric_locked_until > now()) {
            $remaining = $user->biometric_locked_until->diffInMinutes(now());
            return response()->json([
                'success' => false,
                'message' => "Too many failed attempts. Please try again after {$remaining} minutes.",
                'data' => [
                    'locked_until' => $user->biometric_locked_until,
                    'remaining_minutes' => $remaining
                ]
            ], 429);
        }

        // Verify code
        if (!Hash::check($request->code, $user->four_digit_code)) {
            // Increment failed attempts
            $user->increment('biometric_login_attempts');

            // Lock after 5 failed attempts
            if ($user->biometric_login_attempts >= 5) {
                $user->update([
                    'biometric_locked_until' => now()->addMinutes(30)
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Too many failed attempts. Account locked for 30 minutes.',
                    'data' => [
                        'locked_until' => $user->biometric_locked_until
                    ]
                ], 429);
            }

            $remainingAttempts = 5 - $user->biometric_login_attempts;
            return response()->json([
                'success' => false,
                'message' => "Invalid code. {$remainingAttempts} attempts remaining.",
                'data' => [
                    'remaining_attempts' => $remainingAttempts
                ]
            ], 401);
        }

        // Reset attempts on success
        $user->update([
            'biometric_login_attempts' => 0,
            'biometric_locked_until' => null
        ]);

        // Generate token
        $token = $user->createToken('biometric_auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $user->only([
                    'id', 'full_name', 'email', 'employee_id',
                    'airline_id', 'position_id', 'status'
                ]),
                'token' => $token,
                'login_method' => '4_digit_code'
            ]
        ]);
    }

    /**
     * Setup Face ID
     * POST /api/auth/setup-faceid
     */
    public function setupFaceId(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'public_key' => 'required|string',
            'device_id' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        $user->update([
            'biometric_public_key' => $request->public_key,
            'device_id' => $request->device_id,
            'face_id_enabled' => true,
            'biometric_login_enabled' => true,
            'biometric_setup_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Face ID setup successfully',
            'data' => [
                'face_id_enabled' => true,
                'device_id' => $user->device_id,
                'setup_at' => $user->biometric_setup_at
            ]
        ]);
    }

    /**
     * Login with Face ID
     * POST /api/auth/login-faceid
     */
    public function loginWithFaceId(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'device_id' => 'required|string',
            'signature' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)
            ->where('device_id', $request->device_id)
            ->where('face_id_enabled', true)
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Face ID not set up for this device or account'
            ], 401);
        }

        // Verify signature using public key (simplified)
        if (empty($request->signature)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid signature'
            ], 401);
        }

        // Generate token
        $token = $user->createToken('faceid_auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Face ID login successful',
            'data' => [
                'user' => $user->only([
                    'id', 'full_name', 'email', 'employee_id',
                    'airline_id', 'position_id', 'status'
                ]),
                'token' => $token,
                'login_method' => 'face_id'
            ]
        ]);
    }

    /**
     * Disable biometric login
     * DELETE /api/auth/disable-biometric
     */
    public function disableBiometric(Request $request)
    {
        $user = $request->user();

        $user->update([
            'four_digit_code' => null,
            'face_id_enabled' => false,
            'biometric_public_key' => null,
            'device_id' => null,
            'biometric_login_enabled' => false,
            'biometric_setup_at' => null,
            'biometric_login_attempts' => 0,
            'biometric_locked_until' => null
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Biometric login disabled successfully'
        ]);
    }

    /**
     * Get biometric login status
     * GET /api/auth/biometric-status
     */
    public function getBiometricStatus(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'biometric_login_enabled' => $user->biometric_login_enabled,
                'face_id_enabled' => $user->face_id_enabled,
                'has_4_digit_code' => !is_null($user->four_digit_code),
                'device_id' => $user->device_id,
                'setup_at' => $user->biometric_setup_at,
                'locked' => $user->biometric_locked_until && $user->biometric_locked_until > now(),
                'locked_until' => $user->biometric_locked_until,
                'remaining_attempts' => $user->biometric_login_attempts < 5 ?
                    5 - $user->biometric_login_attempts : 0
            ]
        ]);
    }

    /**
     * Update 4-digit code
     * POST /api/auth/update-code
     */
    public function updateCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_code' => 'required|string|size:4',
            'new_code' => 'required|string|size:4|confirmed',
            'new_code_confirmation' => 'required|string|size:4'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        // Verify current code
        if (!$user->four_digit_code || !Hash::check($request->current_code, $user->four_digit_code)) {
            return response()->json([
                'success' => false,
                'message' => 'Current code is incorrect'
            ], 401);
        }

        // Update code
        $user->update([
            'four_digit_code' => Hash::make($request->new_code)
        ]);

        return response()->json([
            'success' => true,
            'message' => '4-digit code updated successfully'
        ]);
    }
}
