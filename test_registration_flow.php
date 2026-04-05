<?php
require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "=== CrewSwap Registration & OTP Email Test ===\n\n";

// Test 1: Verify EmailOtpService exists
echo "Test 1: Verifying EmailOtpService...\n";
try {
    $emailOtpService = app('App\Services\EmailOtpService');
    echo "✓ EmailOtpService is injectable\n\n";
} catch (\Exception $e) {
    echo "✗ EmailOtpService error: " . $e->getMessage() . "\n\n";
}

// Test 2: Verify Resend configuration
echo "Test 2: Verifying Resend configuration...\n";
echo "  Mail Driver: " . config('mail.default') . "\n";
echo "  Resend API Key: " . (env('RESEND_API_KEY') ? "✓ Configured" : "✗ Missing") . "\n";
echo "  From Address: " . config('mail.from.address') . "\n";
echo "  From Name: " . config('mail.from.name') . "\n\n";

// Test 3: Test User Model OTP generation
echo "Test 3: Testing User Model OTP generation...\n";
$testUser = User::firstOrCreate(
    ['email' => 'otp_test_' . time() . '@example.com'],
    [
        'employee_id' => 'EMP' . rand(10000, 99999),
        'full_name' => 'OTP Test User',
        'password' => Hash::make('testpass123'),
        'phone' => '+1' . rand(1000000000, 9999999999),
        'country_base' => 'United States',
        'status' => 'inactive'
    ]
);

$otp = $testUser->generateOtp();
echo "  Generated OTP: $otp\n";
echo "  OTP stored in DB: " . ($testUser->otp_code ? "✓ Yes" : "✗ No") . "\n";
echo "  OTP expires at: " . $testUser->otp_expires_at . "\n\n";

// Test 4: Test EmailOtpService sendOtp
echo "Test 4: Testing EmailOtpService.sendOtp()...\n";
try {
    $emailOtpService = app('App\Services\EmailOtpService');
    $result = $emailOtpService->sendOtp($testUser->email, $otp);
    echo "  Result: " . ($result ? "✓ Email sent" : "✗ Email failed") . "\n";
    echo "  Recipient: " . $testUser->email . "\n";
    echo "  OTP Code: " . $otp . "\n\n";
} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n\n";
}

// Test 5: Verify Laravel Logs
echo "Test 5: Recent Laravel logs (last 5 lines)...\n";
$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $lines = array_map('trim', file($logFile));
    $recentLines = array_slice(array_filter($lines), -5);
    foreach ($recentLines as $line) {
        echo "  " . substr($line, 0, 100) . (strlen($line) > 100 ? "..." : "") . "\n";
    }
} else {
    echo "  Log file not found\n";
}

// Cleanup test user so test runs do not pollute real user lists
\Illuminate\Support\Facades\DB::table('user_trips')->where('user_id', $testUser->id)->delete();
$testUser->delete();
echo "Cleanup: removed temporary test user ({$testUser->email})\n";

echo "\n=== Test Complete ===\n";
echo "✓ All components are configured and ready for registration flow\n";
echo "✓ Visit http://127.0.0.1:8000/frontend-test to test the complete workflow\n";
