<?php
// Test database connection
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    echo "\n=== DATABASE CONNECTION TEST ===\n\n";
    
    // Test 1: Try to get PDO
    $pdo = DB::connection()->getPdo();
    echo "✅ PDO Connection: SUCCESS\n";
    
    // Test 2: Try simple query
    $result = DB::select('SELECT 1');
    echo "✅ Test Query: SUCCESS\n";
    
    // Test 3: Check if migrations table exists
    if (\Illuminate\Support\Facades\Schema::hasTable('migrations')) {
        echo "✅ Migrations Table: EXISTS\n";
    } else {
        echo "❌ Migrations Table: MISSING (need to run migrations)\n";
    }
    
    // Test 4: Count users
    $users = \App\Models\User::count();
    echo "✅ User Count: $users\n";
    
    echo "\n=== ALL TESTS PASSED ===\n";
    
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Code: " . $e->getCode() . "\n";
    exit(1);
}
?>
