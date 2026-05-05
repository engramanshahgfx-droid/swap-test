<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Filament\Facades\Filament;

class TestFilamentFlow extends Command
{
    protected $signature = 'test:filament-flow';
    protected $description = 'Test the exact Filament login flow';

    public function handle()
    {
        $email = 'admin@flightSwap .com';
        $password = 'password';

        $this->info('=== Testing Filament Login Flow ===');
        $this->line("\nStep 1: Get user from database");
        $user = \App\Models\User::where('email', $email)->first();
        if (!$user) {
            $this->error("User not found");
            return;
        }
        $this->info("✓ User found: {$user->full_name} (ID: {$user->id})");

        $this->line("\nStep 2: Test Filament::auth()->attempt()");
        $credentials = ['email' => $email, 'password' => $password];
        $result = Filament::auth()->attempt($credentials, false);
        $this->info("Filament::auth()->attempt() = " . ($result ? "TRUE" : "FALSE"));

        if (!$result) {
            $this->error("Authentication failed!"); return;
        }

        $this->line("\nStep 3: Get authenticated user");
        $auth_user = Filament::auth()->user();
        if (!$auth_user) {
            $this->error("No user after auth");
            return;
        }
        $this->info("✓ Authenticated user: {$auth_user->email}");

        $this->line("\nStep 4: Check FilamentUser interface");
        $is_filament = $auth_user instanceof \Filament\Models\Contracts\FilamentUser;
        $this->info("Implements FilamentUser: " . ($is_filament ? "YES" : "NO"));

        if ($is_filament) {
            $this->line("\nStep 5: Get current panel");
            $panel = Filament::getCurrentPanel();
            $panel_id = $panel ? $panel->getId() : "NULL";
            $this->info("Current panel ID: {$panel_id}");

            $this->line("\nStep 6: Check canAccessPanel()");
            $can_access = $auth_user->canAccessPanel($panel);
            $this->info("canAccessPanel() = " . ($can_access ? "TRUE" : "FALSE"));

            if (!$can_access) {
        $this->error("User cannot access panel!");
        \Log::error("Panel access denied for: " . $auth_user->email);
                // Debug
                $this->line("\nDebug info:");
                $this->line("Roles: " . implode(', ', $auth_user->getRoleNames()->toArray()));
                $this->line("has super-admin: " . ($auth_user->hasRole('super-admin') ? 'YES' : 'NO'));
                return;
            }
        }

        $this->info("\n✓ Login flow succeeded!");
        Filament::auth()->logout();
    }
}
