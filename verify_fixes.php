<?php

require_once 'vendor/autoload.php';

echo "\n" . str_repeat("=" , 70) . "\n";
echo "✅ COMPREHENSIVE API FIX VERIFICATION\n";
echo str_repeat("=", 70) . "\n";

$baseUrl = 'http://127.0.0.1:8000/api';
$tests_passed = 0;
$tests_total = 0;

function makeRequest($method, $endpoint, $data = null, $token = null) {
    global $baseUrl;
    $url = $baseUrl . $endpoint;
    
    $options = [
        'http' => [
            'method' => $method,
            'header' => [
                'Content-Type: application/json',
                'Accept: application/json',
                'User-Agent: VerificationScript/1.0'
            ]
        ]
    ];
    
    if ($token) {
        $options['http']['header'][] = 'Authorization: Bearer ' . $token;
    }
    
    if ($data && ($method === 'POST' || $method === 'PUT')) {
        $options['http']['content'] = json_encode($data);
    }
    
    $context = stream_context_create($options);
    $response = @file_get_contents($url, false, $context);
    
    $status = 0;
    foreach ($http_response_header as $header) {
        if (strpos($header, 'HTTP') === 0) {
            preg_match('/HTTP\/\d+\.\d+ (\d+)/', $header, $matches);
            $status = (int)$matches[1];
            break;
        }
    }
    
    return [
        'status' => $status,
        'data' => json_decode($response, true),
    ];
}

// ====== FIX 1: Registration-Option Alias ======
echo "\n📌 FIX 1: Registration Options Endpoint Alias\n";
echo str_repeat("-", 70) . "\n";
$tests_total++;

$response = makeRequest('GET', '/registration-option');
if ($response['status'] === 200) {
    echo "✅ PASS: /api/registration-option returns 200\n";
    echo "   - Airlines: " . count($response['data']['data']['airlines'] ?? []) . " available\n";
    echo "   - Positions: " . count($response['data']['data']['positions'] ?? []) . " available\n";
    echo "   - Plane Types: " . count($response['data']['data']['plane_types'] ?? []) . " available\n";
    $tests_passed++;
} else {
    echo "❌ FAIL: /api/registration-option returned " . $response['status'] . "\n";
}

// ====== FIX 2: Profile Enrichment Fields ======
echo "\n📌 FIX 2: User Profile Enrichment with Company/Position Names\n";
echo str_repeat("-", 70) . "\n";
$tests_total++;

// Register a test user
$email = 'enrichtest' . time() . '@crewswap.com';
$regResp = makeRequest('POST', '/simple-register', [
    'email' => $email,
    'password' => 'TestPass123!',
    'full_name' => 'Enrichment Tester'
]);

if ($regResp['status'] === 201) {
    $token = $regResp['data']['data']['token'];
    $profileResp = makeRequest('GET', '/user', null, $token);
    
    if ($profileResp['status'] === 200) {
        $profile = $profileResp['data'];
        
        // Check for enriched fields
        $hasCompanyId = array_key_exists('company_id', $profile);
        $hasCompanyName = array_key_exists('company_name', $profile);
        $hasPositionName = array_key_exists('position_name', $profile);
        
        if ($hasCompanyId && $hasCompanyName && $hasPositionName) {
            echo "✅ PASS: Profile includes enrichment fields\n";
            echo "   - company_id: " . ($profile['company_id'] ?? 'null') . "\n";
            echo "   - company_name: " . ($profile['company_name'] ?? 'null') . "\n";
            echo "   - position_name: " . ($profile['position_name'] ?? 'null') . "\n";
            $tests_passed++;
        } else {
            echo "❌ FAIL: Missing enrichment fields\n";
            echo "   - company_id: " . ($hasCompanyId ? "✓" : "✗") . "\n";
            echo "   - company_name: " . ($hasCompanyName ? "✓" : "✗") . "\n";
            echo "   - position_name: " . ($hasPositionName ? "✓" : "✗") . "\n";
        }
    }
} else {
    echo "❌ FAIL: Could not create test user\n";
}

// ====== FIX 3: Report-User No More 500 Error ======
echo "\n📌 FIX 3: Report-User Endpoint (Safe Role Handling)\n";
echo str_repeat("-", 70) . "\n";
$tests_total++;

// Need two test users - one to report another
$email1 = 'reporter' . time() . '@crewswap.com';
$reg1 = makeRequest('POST', '/simple-register', [
    'email' => $email1,
    'password' => 'TestPass123!',
    'full_name' => 'Reporter'
]);

$email2 = 'reported' . time() . '@crewswap.com';
$reg2 = makeRequest('POST', '/simple-register', [
    'email' => $email2,
    'password' => 'TestPass123!',
    'full_name' => 'Reported User'
]);

if ($reg1['status'] === 201 && $reg2['status'] === 201) {
    $token1 = $reg1['data']['data']['token'];
    $userId2 = $reg2['data']['data']['user']['id'];
    
    $reportResp = makeRequest('POST', '/report-user', [
        'reported_user_id' => $userId2,
        'reason' => 'spam',
        'details' => 'Test report'
    ], $token1);
    
    if ($reportResp['status'] === 201) {
        echo "✅ PASS: /api/report-user returns 201 (created)\n";
        echo "   - No 500 error with missing roles\n";
        echo "   - Report safely created\n";
        $tests_passed++;
    } else if ($reportResp['status'] === 500) {
        echo "❌ FAIL: /api/report-user still returns 500\n";
        echo "   - Error: " . ($reportResp['data']['message'] ?? 'Unknown') . "\n";
    } else {
        echo "⚠️  WARNING: /api/report-user returns " . $reportResp['status'] . "\n";
        echo "   - Message: " . ($reportResp['data']['message'] ?? 'Unknown') . "\n";
    }
} else {
    echo "⚠️  WARNING: Could not create test users for report testing\n";
}

// ====== SUMMARY ======
echo "\n" . str_repeat("=", 70) . "\n";
echo "VERIFICATION SUMMARY\n";
echo str_repeat("=", 70) . "\n";
echo "Tests Passed: " . $tests_passed . " / " . $tests_total . "\n";

if ($tests_passed === $tests_total) {
    echo "\n🎉 ALL FIXES VERIFIED SUCCESSFULLY!\n";
} else {
    echo "\n⚠️  Some tests did not pass. Review output above.\n";
}

echo "\nFIXES IMPLEMENTED:\n";
echo "  ✓ Fix 1: Added /api/registration-option endpoint alias\n";
echo "  ✓ Fix 2: Added company_id, company_name, position_name fields to profile\n";
echo "  ✓ Fix 3: Safe role checking in report-user to prevent 500 errors\n";

echo "\n" . str_repeat("=", 70) . "\n\n";
?>
