<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Exception;

/**
 * Firebase Authentication Service
 * Handles Firebase token verification and user management
 */
class FirebaseAuthService
{
    protected $projectId;
    protected $discoveryURL;
    protected $publicKeys = [];
    protected $keysCacheDuration = 3600; // 1 hour

    public function __construct()
    {
        $this->projectId = config('services.firebase.project_id');
        $this->discoveryURL = 'https://www.googleapis.com/robot/v1/metadata/x509/securetoken@system.gserviceaccount.com';
    }

    /**
     * Verify a Firebase ID token
     * @param string $idToken - Firebase ID token from client
     * @return array|false - Decoded token payload or false if invalid
     */
    public function verifyIdToken($idToken)
    {
        try {
            // Check if project ID is configured
            if (!$this->projectId) {
                \Log::error('Firebase project ID not configured');
                return false;
            }

            // Get Firebase public keys
            $publicKeys = $this->getFirebasePublicKeys();
            
            if (empty($publicKeys)) {
                \Log::error('Could not fetch Firebase public keys');
                return false;
            }

            // Decode JWT header to get the key ID
            $headerDot = strpos($idToken, '.');
            if ($headerDot === false) {
                \Log::error('Invalid token format: no dot found');
                return false;
            }

            $header = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], substr($idToken, 0, $headerDot))), true);

            if (!isset($header['kid'])) {
                \Log::error('Token header missing kid');
                return false;
            }

            if (!isset($publicKeys[$header['kid']])) {
                \Log::error('Public key not found for kid: ' . $header['kid']);
                return false;
            }

            // Verify and decode the token
            $key = new Key($publicKeys[$header['kid']], 'RS256');
            $decoded = JWT::decode($idToken, $key);

            // Verify the project ID (audience claim)
            if (!isset($decoded->aud) || $decoded->aud !== $this->projectId) {
                \Log::error('Token audience does not match. Expected: ' . $this->projectId . ', Got: ' . ($decoded->aud ?? 'null'));
                return false;
            }

            // Token is valid
            return (array) $decoded;
        } catch (Exception $e) {
            \Log::error('Firebase token verification failed: ' . $e->getMessage() . ' | Token prefix: ' . substr($idToken ?? 'null', 0, 50));
            return false;
        }
    }

    /**
     * Get Firebase public keys for token verification
     * @return array - Public keys array
     */
    protected function getFirebasePublicKeys()
    {
        // Try to get from cache first
        $cacheKey = 'firebase_public_keys';
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $client = new Client();
            $response = $client->request('GET', $this->discoveryURL);
            $publicKeys = json_decode((string) $response->getBody(), true);

            // Cache the keys
            Cache::put($cacheKey, $publicKeys, $this->keysCacheDuration);

            return $publicKeys;
        } catch (Exception $e) {
            \Log::error('Failed to fetch Firebase public keys: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Verify custom claims in the token
     * @param array $decodedToken - Decoded token payload
     * @param string $claim - Claim name
     * @param mixed $expectedValue - Expected claim value
     * @return bool
     */
    public function verifyClaim($decodedToken, $claim, $expectedValue)
    {
        return isset($decodedToken[$claim]) && $decodedToken[$claim] === $expectedValue;
    }

    /**
     * Get user ID from token
     * @param array $decodedToken - Decoded token payload
     * @return string - Firebase UID
     */
    public function getUserId($decodedToken)
    {
        return $decodedToken['sub'] ?? null;
    }

    /**
     * Get user email from token
     * @param array $decodedToken - Decoded token payload
     * @return string|null - User email or null
     */
    public function getUserEmail($decodedToken)
    {
        return $decodedToken['email'] ?? null;
    }

    /**
     * Get user display name from token
     * @param array $decodedToken - Decoded token payload
     * @return string|null - User display name or null
     */
    public function getUserDisplayName($decodedToken)
    {
        return $decodedToken['name'] ?? null;
    }
}
