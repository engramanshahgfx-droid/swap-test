<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckDatabase extends Command
{
    protected $signature = 'check:db';
    protected $description = 'Check database admin user details';

    public function handle()
    {
        $user = DB::table('users')
            ->where('email', 'admin@flightSwap .com')
            ->first();

        if (!$user) {
            $this->error('No user found');
            return;
        }

        $this->info('User in database:');
        foreach ((array)$user as $key => $value) {
            if ($key === 'password') {
                $this->line("  {$key}: " . substr($value, 0, 20) . '...');
            } else {
                $this->line("  {$key}: {$value}");
            }
        }
    }
}
