<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $user = \App\Models\User::find(1);
    if (!$user) {
        echo "User not found\n";
        exit(1);
    }
    
    echo "User ID: " . $user->id . "\n";
    echo "User Name: " . $user->full_name . "\n";
    
    echo "\nChecking roles...\n";
    $start = microtime(true);
    $roles = $user->roles()->limit(10)->pluck('name')->toArray();
    $elapsed = microtime(true) - $start;
    
    echo "Roles found (" . $elapsed . "s): " . json_encode($roles) . "\n";
    
    echo "\nTesting canAccessPanel...\n";
    $start = microtime(true);
    $canAccess = $user->canAccessPanel(new \Filament\Panel());
    $elapsed = microtime(true) - $start;
    
    echo "Result (" . $elapsed . "s): " . ($canAccess ? 'YES' : 'NO') . "\n";
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    exit(1);
}
