<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

class TestAuth extends Command
{
    protected $signature = 'auth:test {email} {password}';
    protected $description = 'Test authentication';

    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');

        // Test direct authentication
        $authenticated = Auth::attempt(['email' => $email, 'password' => $password]);
        
        if ($authenticated) {
            $this->info("✓ Authentication successful");
            $user = Auth::user();
            $this->info("  User: {$user->full_name}");
        } else {
            $this->error("✗ Authentication failed");
            
            // Check user exists
            $user = User::where('email', $email)->first();
            if ($user) {
                $this->info("✓ User exists: {$user->full_name}");
                $this->info("  Status: {$user->status}");
                $this->info("  Email verified: " . ($user->email_verified_at ? 'YES' : 'NO'));
            } else {
                $this->error("✗ User does not exist");
            }
        }
    }
}
