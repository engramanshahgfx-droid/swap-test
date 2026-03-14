<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class CheckAdminRole extends Command
{
    protected $signature = 'check:admin-role';
    protected $description = 'Check admin user role and permissions';

    public function handle()
    {
        $user = User::find(1);
        
        if (!$user) {
            $this->error("Admin user not found");
            return;
        }
        
        $this->info("Admin User Details:");
        $this->line("ID: {$user->id}");
        $this->line("Email: {$user->email}");
        $this->line("Name: {$user->full_name}");
        $this->line("Status: {$user->status}");
        $this->line("Email Verified: " . ($user->email_verified_at ? 'YES' : 'NO'));
        
        // Check roles
        $roles = $user->getRoleNames();
        $this->info("\nRoles: " . ($roles->count() > 0 ? implode(', ', $roles->toArray()) : 'NONE'));
        
        // Check canAccessPanel
        $can_access = $user->canAccessPanel(new \Filament\Panel());
        $this->info("Can Access Panel: " . ($can_access ? 'YES' : 'NO'));
        
        // Check hasRole
        $this->info("\nhasRole('super-admin'): " . ($user->hasRole('super-admin') ? 'YES' : 'NO'));
        $this->info("hasRole('crew_manager'): " . ($user->hasRole('crew_manager') ? 'YES' : 'NO'));
        
        // Check password
        $password_correct = password_verify('password', $user->password);
        $this->info("\nPassword 'password' matches: " . ($password_correct ? 'YES' : 'NO'));
    }
}
