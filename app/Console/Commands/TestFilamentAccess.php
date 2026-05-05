<?php

namespace App\Console\Commands;

use App\Models\User;
use Filament\Panel;
use Illuminate\Console\Command;

class TestFilamentAccess extends Command
{
    protected $signature = 'test:filament-access';
    protected $description = 'Test Filament panel access';

    public function handle()
    {
        $user = User::where('email', 'admin@flightSwap .com')->first();

        if (!$user) {
            $this->error('User not found');
            return;
        }

        $this->info('User: ' . $user->full_name);
        $this->info('Email: ' . $user->email);
        $this->info('Roles: ' . $user->getRoleNames()->implode(', '));
        $this->info('Has super-admin: ' . ($user->hasRole('super-admin') ? 'YES' : 'NO'));

        // Check if user can access panel
        if (method_exists($user, 'canAccessPanel')) {
            try {
                // We need a dummy panel to test
                $this->info('canAccessPanel method exists: YES');
            } catch (\Exception $e) {
                $this->error('Error calling canAccessPanel: ' . $e->getMessage());
            }
        } else {
            $this->error('canAccessPanel method missing');
        }
    }
}
