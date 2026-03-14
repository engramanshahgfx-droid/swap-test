<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

class TestFilamentAuth extends Command
{
    protected $signature = 'test:filament-auth';
    protected $description = 'Test Filament authentication flow';

    public function handle()
    {
        $email = 'admin@crewswap.com';
        $password = 'password';

        $this->info('Testing Filament authentication...');
        $this->line("Email: {$email}");
        $this->line("Password: {$password}");

        // Test with web guard (what Filament uses)
        $this->line("\n[TEST 1] Using Auth::attempt with web guard:");
        if (Auth::attempt(['email' => $email, 'password' => $password], false, false)) {
            $this->info("✓ Authenticated successfully");
            $user = Auth::user();
            $this->line("User: {$user->full_name}");
            $this->line("Can access panel: " . ($user->canAccessPanel(new \Filament\Panel()) ? 'YES' : 'NO'));
            Auth::logout();
        } else {
            $this->error("✗ Authentication failed");
        }

        // Test with remember option
        $this->line("\n[TEST 2] Using Auth::attempt with remember:");
        if (Auth::attempt(['email' => $email, 'password' => $password], false)) {
            $this->info("✓ Authenticated successfully");
            Auth::logout();
        } else {
            $this->error("✗ Authentication failed");
        }

        // Test direct authentication
        $this->line("\n[TEST 3] Direct model authentication:");
        $user = \App\Models\User::where('email', $email)->first();
        if ($user && password_verify($password, $user->password)) {
            $this->info("✓ Password verification successful");
        } else {
            $this->error("✗ Password verification failed");
        }
    }
}
