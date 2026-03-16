<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$admin = User::where('email', 'admin@crewswap.com')->first();

if ($admin) {
    // Update password to password
    $admin->update(['password' => Hash::make('password')]);
    echo "✓ Admin password updated to: password\n";
    echo "Email: admin@crewswap.com\n";
    echo "Status: " . $admin->status . "\n";
} else {
    echo "Admin user not found!\n";
    exit(1);
}
