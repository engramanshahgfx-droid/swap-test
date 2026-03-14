<?php

namespace App\Http\Middleware;

use App\Services\FirebaseAuthService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to verify Firebase authentication tokens
 * Validates Firebase ID tokens sent in the Authorization header
 * Falls back to Laravel Sanctum auth if Firebase token is invalid
 */
class VerifyFirebaseToken
{
    protected $firebaseService;

    public function __construct(FirebaseAuthService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    public function handle(Request $request, Closure $next): Response
    {
        // Get the Authorization header
        $authHeader = $request->header('Authorization');

        if (!empty($authHeader)) {
            // Extract the token (format: "Bearer <token>")
            $token = str_replace('Bearer ', '', $authHeader);

            // Try to verify Firebase token
            $decodedToken = $this->firebaseService->verifyIdToken($token);

            if ($decodedToken) {
                // Firebase token is valid
                // Store the decoded token in request for later use
                $request->merge([
                    'firebase_user' => [
                        'uid' => $this->firebaseService->getUserId($decodedToken),
                        'email' => $this->firebaseService->getUserEmail($decodedToken),
                        'name' => $this->firebaseService->getUserDisplayName($decodedToken),
                    ],
                    'firebase_token' => $decodedToken
                ]);

                return $next($request);
            }
        }

        // If no valid Firebase token, check for Sanctum token (fallback)
        // This allows existing Laravel Sanctum users to continue working
        if ($this->hasValidSanctumToken($request)) {
            return $next($request);
        }

        // No valid authentication found
        return response()->json([
            'message' => 'Unauthorized - Invalid or missing authentication token'
        ], 401);
    }

    /**
     * Check if request has valid Sanctum authentication
     */
    private function hasValidSanctumToken(Request $request): bool
    {
        return $request->user() !== null;
    }
}
