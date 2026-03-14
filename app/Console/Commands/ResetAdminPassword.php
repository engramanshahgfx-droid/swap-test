<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class ResetAdminPassword extends Command
{
    protected $signature = 'admin:reset-password {password=password}';
    protected $description = 'Reset admin password';

    public function handle()
    {
        $user = User::where('email', 'admin@crewswap.com')->first();

        if (!$user) {
            $this->error('Admin user not found');
            return;
        }

        $password = $this->argument('password');
        $user->update(['password' => Hash::make($password)]);

        $this->info("✓ Admin password reset successfully");
        $this->info("  Email: {$user->email}");
        $this->info("  Password: {$password}");
    }
}
