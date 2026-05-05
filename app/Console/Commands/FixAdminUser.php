<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class FixAdminUser extends Command
{
    protected $signature = 'fix:admin';
    protected $description = 'Fix admin user verification status';

    public function handle()
    {
        $user = User::where('email', 'admin@flightSwap .com')->first();

        if (!$user) {
            $this->error('Admin user not found');
            return;
        }

        // Update the user to ensure all fields are correct
        $user->update([
            'status' => 'active',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);

        $this->info('✓ Admin user fixed');
        $this->info("  Email: {$user->email}");
        $this->info("  Status: {$user->status}");
        $this->info("  Email Verified: " . ($user->email_verified_at ? 'YES' : 'NO'));
    }
}
