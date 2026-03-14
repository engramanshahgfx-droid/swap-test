<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class CheckAdminUser extends Command
{
    protected $signature = 'check:admin';
    protected $description = 'Check admin user credentials';

    public function handle()
    {
        $user = User::where('email', 'admin@crewswap.com')->first();

        if ($user) {
            $this->info("✓ User found: {$user->full_name}");
            $this->info("  Email: {$user->email}");
            $this->info("  Password hash exists: " . (strlen($user->password) > 0 ? 'YES' : 'NO'));
            $this->info("  Password match: " . (password_verify('password', $user->password) ? 'YES' : 'NO'));
        } else {
            $this->error('✗ Admin user not found');
            $this->info('Available users:');
            User::all(['id', 'email', 'full_name'])->each(function ($u) {
                $this->line("  {$u->id}. {$u->email} - {$u->full_name}");
            });
        }
    }
}
