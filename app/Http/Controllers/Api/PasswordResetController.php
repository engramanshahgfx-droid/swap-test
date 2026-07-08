<?php
// app/Http/Controllers/Api/PasswordResetController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;

class PasswordResetController extends Controller
{
    /**
     * Send password reset link to email
     * POST /api/password/forgot
     */
    public function sendResetLink(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();
        $token = Str::random(60);

        $user->update([
            'reset_password_token' => Hash::make($token),
            'reset_password_expires_at' => now()->addHours(24)
        ]);

        // Send email with reset link
        $this->sendResetEmail($user, $token);

        return response()->json([
            'success' => true,
            'message' => 'Password reset link sent to your email',
            'data' => [
                'email' => $user->email,
                'token_expires_in' => '24 hours'
            ]
        ]);
    }

    /**
     * Reset password using token
     * POST /api/password/reset
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string|min:8'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)
            ->whereNotNull('reset_password_token')
            ->where('reset_password_expires_at', '>', now())
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token. Please request a new reset link.'
            ], 400);
        }

        // Verify token
        if (!Hash::check($request->token, $user->reset_password_token)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid token provided'
            ], 400);
        }

        // Update password
        $user->update([
            'password' => Hash::make($request->password),
            'reset_password_token' => null,
            'reset_password_expires_at' => null,
            'password_changed_at' => now()
        ]);

        // Revoke all existing tokens (optional - security)
        $user->tokens()->delete();

        // Generate new token for auto-login
        $newToken = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully',
            'data' => [
                'user' => $user->only(['id', 'full_name', 'email']),
                'token' => $newToken
            ]
        ]);
    }

    /**
     * Change password (authenticated user)
     * POST /api/password/change
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
            'new_password_confirmation' => 'required|string|min:8'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect'
            ], 400);
        }

        // Update password
        $user->update([
            'password' => Hash::make($request->new_password),
            'password_changed_at' => now()
        ]);

        // Optionally revoke all tokens except current
        $user->tokens()->where('id', '!=', $request->user()->currentAccessToken()->id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully'
        ]);
    }

    /**
     * Validate reset token
     * GET /api/password/validate-token
     */
    public function validateToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'email' => 'required|email|exists:users,email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)
            ->whereNotNull('reset_password_token')
            ->where('reset_password_expires_at', '>', now())
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token'
            ], 400);
        }

        if (!Hash::check($request->token, $user->reset_password_token)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid token'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Token is valid',
            'data' => [
                'email' => $user->email,
                'expires_at' => $user->reset_password_expires_at
            ]
        ]);
    }

    /**
     * Resend reset link
     * POST /api/password/resend
     */
    public function resendResetLink(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        // Check if user already has a valid token
        if ($user->reset_password_token && $user->reset_password_expires_at > now()) {
            // Resend same token
            $token = $request->token ?? Str::random(60);
        } else {
            // Generate new token
            $token = Str::random(60);
            $user->update([
                'reset_password_token' => Hash::make($token),
                'reset_password_expires_at' => now()->addHours(24)
            ]);
        }

        $this->sendResetEmail($user, $token);

        return response()->json([
            'success' => true,
            'message' => 'Reset link resent successfully'
        ]);
    }

    /**
     * Send password reset email
     */
    private function sendResetEmail(User $user, string $token)
    {
        $resetUrl = config('app.frontend_url') . '/reset-password?token=' . $token . '&email=' . urlencode($user->email);

        try {
            Mail::send('emails.password-reset', [
                'user' => $user,
                'token' => $token,
                'resetUrl' => $resetUrl
            ], function ($message) use ($user) {
                $message->to($user->email)
                        ->subject('Reset Your Password - ' . config('app.name'));
            });
        } catch (\Exception $e) {
            // Log error but don't fail the request
            \Log::error('Password reset email failed: ' . $e->getMessage());
        }
    }
}
