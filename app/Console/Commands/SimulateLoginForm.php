<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SimulateLoginForm extends Command
{
    protected $signature = 'test:simulate-login {email=admin@flightSwap .com} {password=password}';
    protected $description = 'Simulate the login form submission';

    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');

        $this->info("Simulating form submission with:");
        $this->line("Email: {$email}");
        $this->line("Password: {$password}");

        // Check user
        $user = \App\Models\User::where('email', $email)->first();
        if (!$user) {
            $this->error("User not found");
            return;
        }

        $this->line("\nUser found:");
        $this->line("ID: {$user->id}");
        $this->line("Email: {$user->email}");
        $this->line("Email verified: " . ($user->email_verified_at ? "YES" : "NO"));

        // Test password
        $pass_match = Hash::check($password, $user->password);
        $this->line("Password match (Hash::check): " . ($pass_match ? "YES" : "NO"));

        // Test Auth::attempt with different combinations
        $this->line("\n=== Testing Auth::attempt ===");

        // Test 1: Standard attempt
        $result1 = Auth::attempt(['email' => $email, 'password' => $password]);
        $this->line("Auth::attempt([email, password]): " . ($result1 ? "✓ SUCCESS" : "✗ FAIL"));
        if ($result1) Auth::logout();

        // Test 2: With remember false
        $result2 = Auth::attempt(['email' => $email, 'password' => $password], false);
        $this->line("Auth::attempt([email, password], remember=false): " . ($result2 ? "✓ SUCCESS" : "✗ FAIL"));
        if ($result2) Auth::logout();

        // Test 3: With all false
        $result3 = Auth::attempt(['email' => $email, 'password' => $password], false, false);
        $this->line("Auth::attempt([email, password], false, false): " . ($result3 ? "✓ SUCCESS" : "✗ FAIL"));
        if ($result3) Auth::logout();

        // Test 4: Check if user can access panel
        $this->line("\nPanel Access:");
        $this->line("canAccessPanel: " . ($user->canAccessPanel(new \Filament\Panel()) ? "YES" : "NO"));
    }
}
