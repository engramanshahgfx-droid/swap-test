<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class DebugApi extends Command
{
    protected $signature = 'debug:api';
    protected $description = 'Debug API authentication issues';

    public function handle()
    {
        $this->info('=== API Debug ===');

        // Check user exists
        $user = User::where('email', 'admin@crewswap.com')->first();

        if (!$user) {
            $this->error('User admin@crewswap.com not found!');
            return;
        }

        $this->line("✓ User exists: {$user->email}");
        $this->line("  ID: {$user->id}");
        $this->line("  Full Name: {$user->full_name}");

        // Check password
        $result = Hash::check('password', $user->password);
        $this->line("✓ Password check ('password'): " . ($result ? 'PASS' : 'FAIL'));

        if (!$result) {
            $this->error("Password verification failed!");
        }

        // Check API token capability
        $this->line("\n=== Sanctum Tokens ===");
        $token = $user->createToken('test-token')->plainTextToken;
        $this->line("✓ Token created: " . substr($token, 0, 20) . "...");

        // Check current tokens
        $tokens = $user->tokens()->count();
        $this->line("✓ Active tokens: {$tokens}");

        // Check role
        $roles = $user->getRoleNames()->toArray();
        $this->line("\n=== Roles ===");
        $this->line("✓ User roles: " . (count($roles) > 0 ? implode(', ', $roles) : 'None'));

        $this->info("\n✓ User is ready for API testing");
    }
}

