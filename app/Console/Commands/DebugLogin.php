<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class DebugLogin extends Command
{
    protected $signature = 'debug:login';
    protected $description = 'Debug login issue';

    public function handle()
    {
        $this->info('=== Checking User Data ===');
        
        $user = User::where('email', 'admin@crewswap.com')->first();
        
        if (!$user) {
            $this->error('User not found!');
            $this->info('All users:');
            User::all(['id', 'email', 'full_name'])->each(function($u) {
                $this->line("  {$u->email} ({$u->full_name})");
            });
            return;
        }
        
        $this->info("Email: {$user->email}");
        $this->info("Full Name: {$user->full_name}");
        $this->info("Status: {$user->status}");
        $this->info("Roles: " . $user->getRoleNames()->implode(', '));
        $this->info("Email Verified: " . ($user->email_verified_at ? 'YES' : 'NO'));
        
        // Test password
        $plainPassword = "admin123";
        $matches = password_verify($plainPassword, $user->password);
        $this->info("\nPassword Test: {$plainPassword}");
        $this->info("Password Hash Matches: " . ($matches ? 'YES' : 'NO'));
        
        if (!$matches) {
            $this->warn("\n⚠️ Password does not match! Resetting...");
            $user->update(['password' => bcrypt($plainPassword)]);
            $this->info("✓ Password reset to: {$plainPassword}");
        }
    }
}
