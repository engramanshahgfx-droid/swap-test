<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * Firebase Notification Service
 * Uses Firebase Admin SDK to send push notifications via Firebase Cloud Messaging (FCM)
 */
class FirebaseNotificationService
{
    protected $messaging;
    protected $projectId;

    public function __construct()
    {
        $this->projectId = config('services.firebase.project_id');
        
        // Initialize Firebase Admin SDK
        try {
            $serviceAccountPath = config('services.firebase.service_account_key_path');
            
            if (!file_exists($serviceAccountPath)) {
                Log::error('Firebase service account file not found', ['path' => $serviceAccountPath]);
                $this->messaging = null;
                return;
            }

            // Initialize Firebase Admin SDK
            require_once base_path('vendor/autoload.php');
            
            $serviceAccount = json_decode(file_get_contents($serviceAccountPath), true);
            
            // Use Firebase Admin SDK messaging
            $this->messaging = $serviceAccount;
            
        } catch (\Exception $e) {
            Log::error('Failed to initialize Firebase Admin SDK: ' . $e->getMessage());
            $this->messaging = null;
        }
    }

    /**
     * Send notification to a single device using Firebase Cloud Messaging API V1
     *
     * @param string $deviceToken - FCM device token
     * @param string $title - Notification title
     * @param string $body - Notification body
     * @param array $data - Additional data to send with notification
     * @return bool - Success status
     */
    public function sendToDevice($deviceToken, $title, $body, $data = [])
    {
        try {
            if (!$this->messaging) {
                Log::error('Firebase messaging not initialized');
                return false;
            }

            $accessToken = $this->getAccessToken();
            
            if (!$accessToken) {
                Log::error('Failed to get Firebase access token');
                return false;
            }

            $payload = [
                'message' => [
                    'token' => $deviceToken,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'data' => $data,
                ],
            ];

            $response = $this->sendRequest(
                'https://fcm.googleapis.com/v1/projects/' . $this->projectId . '/messages:send',
                $payload,
                $accessToken
            );

            if ($response['success']) {
                Log::info('Notification sent successfully', [
                    'deviceToken' => substr($deviceToken, 0, 20) . '...',
                    'title' => $title,
                ]);
                return true;
            }

            Log::error('Failed to send notification', ['error' => $response['error']]);
            return false;

        } catch (\Exception $e) {
            Log::error('Error sending notification: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send notification to a topic (multiple devices)
     *
     * @param string $topic - FCM topic name
     * @param string $title - Notification title
     * @param string $body - Notification body
     * @param array $data - Additional data
     * @return bool
     */
    public function sendToTopic($topic, $title, $body, $data = [])
    {
        try {
            if (!$this->messaging) {
                Log::error('Firebase messaging not initialized');
                return false;
            }

            $accessToken = $this->getAccessToken();
            
            if (!$accessToken) {
                Log::error('Failed to get Firebase access token');
                return false;
            }

            $payload = [
                'message' => [
                    'topic' => $topic,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'data' => $data,
                ],
            ];

            $response = $this->sendRequest(
                'https://fcm.googleapis.com/v1/projects/' . $this->projectId . '/messages:send',
                $payload,
                $accessToken
            );

            if ($response['success']) {
                Log::info('Topic notification sent successfully', ['topic' => $topic]);
                return true;
            }

            Log::error('Failed to send topic notification', ['error' => $response['error']]);
            return false;

        } catch (\Exception $e) {
            Log::error('Error sending topic notification: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get Firebase access token using service account credentials
     *
     * @return string|null - Access token or null on failure
     */
    protected function getAccessToken()
    {
        try {
            $serviceAccountPath = config('services.firebase.service_account_key_path');
            $serviceAccount = json_decode(file_get_contents($serviceAccountPath), true);

            $now = time();
            $expire = $now + 3600; // 1 hour

            $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
            $payload = json_encode([
                'iss' => $serviceAccount['client_email'],
                'scope' => 'https://www.googleapis.com/auth/cloud-platform',
                'aud' => 'https://oauth2.googleapis.com/token',
                'exp' => $expire,
                'iat' => $now,
            ]);

            $headerEncoded = rtrim(strtr(base64_encode($header), '+/', '-_'), '=');
            $payloadEncoded = rtrim(strtr(base64_encode($payload), '+/', '-_'), '=');
            $signature = $this->signJWT($headerEncoded . '.' . $payloadEncoded, $serviceAccount['private_key']);

            $jwt = $headerEncoded . '.' . $payloadEncoded . '.' . $signature;

            // Exchange JWT for access token
            $ch = curl_init('https://oauth2.googleapis.com/token');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=urn:ietf:params:oauth:grant-type:jwt-bearer&assertion=' . $jwt);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);

            $response = json_decode(curl_exec($ch), true);
            curl_close($ch);

            if (isset($response['access_token'])) {
                return $response['access_token'];
            }

            Log::error('Failed to get access token from OAuth', $response);
            return null;

        } catch (\Exception $e) {
            Log::error('Error getting access token: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Sign JWT with private key
     *
     * @param string $data - Data to sign
     * @param string $privateKey - Private key
     * @return string - Signature
     */
    protected function signJWT($data, $privateKey)
    {
        $signature = '';
        openssl_sign($data, $signature, $privateKey, 'sha256');
        return rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');
    }

    /**
     * Send HTTP request to Firebase API
     *
     * @param string $url
     * @param array $payload
     * @param string $accessToken
     * @return array
     */
    protected function sendRequest($url, $payload, $accessToken)
    {
        try {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $accessToken,
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200) {
                return ['success' => true, 'data' => json_decode($response, true)];
            }

            return ['success' => false, 'error' => json_decode($response, true)];

        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send swap request notification
     */
    public function notifySwapRequest($deviceToken, $crewName, $tripData = [])
    {
        return $this->sendToDevice(
            $deviceToken,
            'New Swap Request',
            "{$crewName} wants to swap trips with you",
            array_merge(['type' => 'swap_request'], $tripData)
        );
    }

    /**
     * Send swap approval notification
     */
    public function notifySwapApproved($deviceToken, $crewName)
    {
        return $this->sendToDevice(
            $deviceToken,
            'Swap Approved',
            "{$crewName} approved your swap request",
            ['type' => 'swap_approved']
        );
    }

    /**
     * Send swap rejection notification
     */
    public function notifySwapRejected($deviceToken, $crewName)
    {
        return $this->sendToDevice(
            $deviceToken,
            'Swap Rejected',
            "{$crewName} rejected your swap request",
            ['type' => 'swap_rejected']
        );
    }

    /**
     * Send new message notification
     */
    public function notifyNewMessage($deviceToken, $senderName, $message)
    {
        return $this->sendToDevice(
            $deviceToken,
            'New Message',
            "{$senderName}: {$message}",
            ['type' => 'new_message']
        );
    }
}
