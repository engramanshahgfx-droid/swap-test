<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Create roles if they don't exist
$superAdminRole = Role::firstOrCreate(
    ['name' => 'super-admin', 'guard_name' => 'web'],
    ['name' => 'super-admin', 'guard_name' => 'web']
);

// Check if admin already exists
$admin = User::where('email', 'admin@flightswap.com')->first();

if ($admin) {
    echo "Admin user already exists.\n";
    if (!$admin->hasRole('super-admin')) {
        $admin->assignRole('super-admin');
        echo "✓ Super-admin role assigned.\n";
    }
} else {
    $admin = User::create([
        'employee_id' => 'ADMIN001',
        'full_name' => 'Admin User',
        'email' => 'admin@flightswap.com',
        'password' => bcrypt('password'),
        'phone' => '+1234567890',
        'country_base' => 'USA',
        'status' => 'active',
        'phone_verified_at' => now(),
    ]);

    $admin->assignRole('super-admin');
    echo "✓ Admin user created successfully!\n";
    echo "Email: admin@flightswap.com\n";
    echo "Password: password\n";
}
