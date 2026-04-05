<?php

require_once 'vendor/autoload.php';

class APITester {
    private $baseUrl = 'http://127.0.0.1:8000/api';
    private $token = null;
    
    public function test() {
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "COMPLETE API TESTING SUITE\n";
        echo str_repeat("=", 60) . "\n";
        
        // Test 1: Get Languages
        $this->testGetLanguages();
        
        // Test 2: Register
        $this->testRegister();
        
        // Test 3: Get User Profile
        if ($this->token) {
            $this->testGetProfile();
        }
        
        // Test 4: Registration Options (both endpoints)
        $this->testRegistrationOptions();
        
        // Test 5: Logout
        if ($this->token) {
            $this->testLogout();
        }
        
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "TESTING COMPLETE\n";
        echo str_repeat("=", 60) . "\n";
    }
    
    private function testGetLanguages() {
        echo "\n[TEST 1] GET /api/languages\n";
        echo "--" . str_repeat("-", 56) . "\n";
        
        $response = $this->makeRequest('GET', '/languages');
        
        if ($response['status'] === 200) {
            echo "✅ SUCCESS (200)\n";
            echo "Supported Languages: ";
            $langs = $response['data']['supported_languages'] ?? [];
            echo implode(', ', array_map(fn($l) => $l['name'], $langs)) . "\n";
        } else {
            echo "❌ FAILED - Status: " . $response['status'] . "\n";
        }
    }
    
    private function testRegister() {
        echo "\n[TEST 2] POST /api/simple-register\n";
        echo "--" . str_repeat("-", 56) . "\n";
        
        $email = 'testuser' . time() . '@crewswap.com';
        $payload = [
            'email' => $email,
            'password' => 'TestPassword123!',
            'full_name' => 'Test User ' . time()
        ];
        
        echo "Registering: " . $email . "\n";
        $response = $this->makeRequest('POST', '/simple-register', $payload);
        
        if ($response['status'] === 200 || $response['status'] === 201) {
            echo "✅ SUCCESS (" . $response['status'] . ")\n";
            $this->token = $response['data']['data']['token'] ?? null;
            $userId = $response['data']['data']['user']['id'] ?? null;
            echo "User ID: $userId\n";
            echo "Token: " . substr($this->token, 0, 25) . "...\n";
        } else {
            echo "❌ FAILED - Status: " . $response['status'] . "\n";
            echo "Error: " . ($response['data']['message'] ?? 'Unknown') . "\n";
        }
    }
    
    private function testGetProfile() {
        echo "\n[TEST 3] GET /api/user (Profile)\n";
        echo "--" . str_repeat("-", 56) . "\n";
        
        $response = $this->makeRequest('GET', '/user', null, $this->token);
        
        if ($response['status'] === 200) {
            echo "✅ SUCCESS (200)\n";
            $data = $response['data'];
            
            // Check for enriched fields
            $fields = ['id', 'email', 'full_name', 'company_id', 'company_name', 'position_name'];
            echo "Response fields:\n";
            foreach ($fields as $field) {
                $value = $data[$field] ?? 'NOT PRESENT';
                $marker = (in_array($field, ['company_id', 'company_name', 'position_name'])) ? '🆕' : '  ';
                echo "  $marker $field: $value\n";
            }
        } else {
            echo "❌ FAILED - Status: " . $response['status'] . "\n";
        }
    }
    
    private function testRegistrationOptions() {
        echo "\n[TEST 4] GET /api/registration-option & /api/registration-options\n";
        echo "--" . str_repeat("-", 56) . "\n";
        
        // Test singular form
        echo "Testing /api/registration-option: ";
        $response1 = $this->makeRequest('GET', '/registration-option');
        if ($response1['status'] === 200) {
            echo "✅ (200)\n";
        } else {
            echo "❌ (" . $response1['status'] . ")\n";
        }
        
        // Test plural form
        echo "Testing /api/registration-options: ";
        $response2 = $this->makeRequest('GET', '/registration-options');
        if ($response2['status'] === 200) {
            echo "✅ (200)\n";
        } else {
            echo "❌ (" . $response2['status'] . ")\n";
        }
        
        if ($response1['status'] === 200) {
            $data = $response1['data']['data'] ?? [];
            echo "Response includes:\n";
            echo "  - Airlines: " . count($data['airlines'] ?? []) . " items\n";
            echo "  - Positions: " . count($data['positions'] ?? []) . " items\n";
            echo "  - Plane Types: " . count($data['plane_types'] ?? []) . " items\n";
        }
    }
    
    private function testLogout() {
        echo "\n[TEST 5] POST /api/logout\n";
        echo "--" . str_repeat("-", 56) . "\n";
        
        $response = $this->makeRequest('POST', '/logout', null, $this->token);
        
        if ($response['status'] === 200) {
            echo "✅ SUCCESS (200)\n";
            echo "Message: " . ($response['data']['message'] ?? 'Logged out') . "\n";
            $this->token = null;
        } else {
            echo "❌ FAILED - Status: " . $response['status'] . "\n";
        }
    }
    
    private function makeRequest($method, $endpoint, $data = null, $token = null) {
        $url = $this->baseUrl . $endpoint;
        
        $options = [
            'http' => [
                'method' => $method,
                'header' => [
                    'Content-Type: application/json',
                    'Accept: application/json',
                    'User-Agent: PHPAPITester/1.0'
                ]
            ]
        ];
        
        if ($token) {
            $options['http']['header'][] = 'Authorization: Bearer ' . $token;
        }
        
        if ($data && ($method === 'POST' || $method === 'PUT')) {
            $options['http']['content'] = json_encode($data);
        }
        
        try {
            $context = stream_context_create($options);
            $response = @file_get_contents($url, false, $context);
            $status = $this->getHttpStatus($http_response_header);
            
            $decoded = json_decode($response, true);
            
            return [
                'status' => $status,
                'data' => $decoded,
                'raw' => $response
            ];
        } catch (\Exception $e) {
            return [
                'status' => 0,
                'data' => ['error' => $e->getMessage()],
                'raw' => null
            ];
        }
    }
    
    private function getHttpStatus(&$headers) {
        foreach ($headers as $header) {
            if (strpos($header, 'HTTP') === 0) {
                preg_match('/HTTP\/\d+\.\d+ (\d+)/', $header, $matches);
                return (int)$matches[1];
            }
        }
        return 0;
    }
}

$tester = new APITester();
$tester->test();
?>
