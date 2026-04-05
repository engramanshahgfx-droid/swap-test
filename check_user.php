<?php

use App\Models\User;

$user = User::where('email', 'admin@crewswap.com')->first();

if ($user) {
    echo "User found: {$user->full_name}\n";
    echo "Email: {$user->email}\n";
    echo "Password hash: " . substr($user->password, 0, 20) . "...\n";
    echo "Password match: " . (password_verify('password', $user->password) ? 'YES' : 'NO') . "\n";
    } 
    else
      {
    echo "Admin user not found\n";
    echo "Available users:\n";
    User::all()->each(function($u) {
        echo "  - {$u->email}\n";
    });
}
