<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Services\FirebaseAuthService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Firebase Authentication Controller
 * Handles user registration, login, and synchronization with Firebase
 */
class FirebaseAuthController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseAuthService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    /**
     * Register a new user with Firebase
     * Creates or links user in Laravel database
     * 
     * POST /api/firebase/register
     */
    public function register(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email',
                'name' => 'required|string|max:255',
                'idToken' => 'required|string'
            ]);

            \Log::info('Firebase registration attempt', [
                'email' => $validated['email'],
                'name' => $validated['name'],
                'tokenLength' => strlen($validated['idToken']),
                'tokenPrefix' => substr($validated['idToken'], 0, 50)
            ]);

            // Verify the Firebase ID token
            $decodedToken = $this->firebaseService->verifyIdToken($validated['idToken']);
            if (!$decodedToken) {
                \Log::error('Firebase token verification failed for registration', [
                    'email' => $validated['email'],
                    'tokenPrefix' => substr($validated['idToken'], 0, 50)
                ]);
                return response()->json([
                    'message' => 'Invalid Firebase token',
                    'details' => 'Token verification failed. Please try registering again.'
                ], 401);
            }

            $firebaseUid = $this->firebaseService->getUserId($decodedToken);
            $email = $this->firebaseService->getUserEmail($decodedToken);

            \Log::info('Firebase token verified', ['uid' => $firebaseUid, 'email' => $email]);

            // Check if user already exists in database
            $user = User::where('firebase_uid', $firebaseUid)
                ->orWhere('email', $email)
                ->first();

            if ($user) {
                // Update existing user with Firebase info
                $user->update([
                    'firebase_uid' => $firebaseUid,
                    'name' => $validated['name'],
                    'email_verified_at' => now()
                ]);
            } else {
                // Create new user
                $user = User::create([
                    'firebase_uid' => $firebaseUid,
                    'name' => $validated['name'],
                    'email' => $email,
                    'email_verified_at' => now(),
                    'password' => bcrypt(str_random(32)) // Random password for Firebase users
                ]);
            }

            \Log::info('User created successfully', ['userId' => $user->id, 'firebaseUid' => $firebaseUid]);

            return response()->json([
                'message' => 'User registered successfully',
                'user' => $user->only(['id', 'firebase_uid', 'email', 'name']),
                'token' => $this->generateAppToken($user)
            ], 201);
        } catch (ValidationException $e) {
            \Log::warning('Validation failed for registration', ['errors' => $e->errors()]);
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Firebase registration error: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return response()->json([
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Login user with Firebase
     * Verifies Firebase token and creates app session
     * 
     * POST /api/firebase/login
     */
    public function login(Request $request)
    {
        try {
            $validated = $request->validate([
                'idToken' => 'required|string'
            ]);

            // Verify the Firebase ID token
            $decodedToken = $this->firebaseService->verifyIdToken($validated['idToken']);
            if (!$decodedToken) {
                return response()->json([
                    'message' => 'Invalid Firebase token'
                ], 401);
            }

            $firebaseUid = $this->firebaseService->getUserId($decodedToken);
            $email = $this->firebaseService->getUserEmail($decodedToken);

            // Find or create user
            $user = User::where('firebase_uid', $firebaseUid)->first();

            if (!$user) {
                // User doesn't exist, create them
                $user = User::create([
                    'firebase_uid' => $firebaseUid,
                    'email' => $email,
                    'name' => $this->firebaseService->getUserDisplayName($decodedToken) ?? 'User',
                    'email_verified_at' => now(),
                    'password' => bcrypt(str_random(32))
                ]);
            } else {
                // Update user info if changed
                $user->update([
                    'email' => $email,
                    'firebase_uid' => $firebaseUid
                ]);
            }

            return response()->json([
                'message' => 'Login successful',
                'user' => $user->only(['id', 'firebase_uid', 'email', 'name']),
                'token' => $this->generateAppToken($user)
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Firebase login error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Login failed'
            ], 500);
        }
    }

    /**
     * Verify Firebase token without creating session
     * Useful for checking token validity
     * 
     * POST /api/firebase/verify
     */
    public function verifyToken(Request $request)
    {
        try {
            $validated = $request->validate([
                'idToken' => 'required|string'
            ]);

            $decodedToken = $this->firebaseService->verifyIdToken($validated['idToken']);
            if (!$decodedToken) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Invalid Firebase token'
                ], 401);
            }

            return response()->json([
                'valid' => true,
                'user' => [
                    'uid' => $this->firebaseService->getUserId($decodedToken),
                    'email' => $this->firebaseService->getUserEmail($decodedToken),
                    'name' => $this->firebaseService->getUserDisplayName($decodedToken)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'valid' => false,
                'message' => 'Token verification failed'
            ], 401);
        }
    }

    /**
     * Link existing Laravel user to Firebase
     * Used for migrating existing users to Firebase
     * 
     * POST /api/firebase/link-account
     */
    public function linkAccount(Request $request)
    {
        try {
            // This endpoint requires existing Sanctum authentication
            if (!auth()->check()) {
                return response()->json([
                    'message' => 'Unauthorized - must be logged in'
                ], 401);
            }

            $validated = $request->validate([
                'idToken' => 'required|string'
            ]);

            $decodedToken = $this->firebaseService->verifyIdToken($validated['idToken']);
            if (!$decodedToken) {
                return response()->json([
                    'message' => 'Invalid Firebase token'
                ], 401);
            }

            $firebaseUid = $this->firebaseService->getUserId($decodedToken);
            $user = auth()->user();

            // Link Firebase UID to existing user
            $user->update(['firebase_uid' => $firebaseUid]);

            return response()->json([
                'message' => 'Account linked successfully',
                'user' => $user->only(['id', 'firebase_uid', 'email', 'name'])
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Firebase account linking error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Account linking failed'
            ], 500);
        }
    }

    /**
     * Generate app authentication token (Sanctum or custom)
     * This allows the app to use existing authentication system
     * while leveraging Firebase for initial authentication
     */
    private function generateAppToken(User $user)
    {
        // Using Laravel Sanctum for mobile/API authentication
        return $user->createToken('auth_token')->plainTextToken;
    }
}
