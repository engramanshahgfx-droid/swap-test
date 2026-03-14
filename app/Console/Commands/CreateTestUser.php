<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateTestUser extends Command
{
    protected $signature = 'create:test-user';
    protected $description = 'Create a test user already verified for API testing';

    public function handle()
    {
        $email = 'aman@test.com';
        $phone = '0551234568'; // Unique phone
        
        // Delete existing test user if exists
        User::where('email', $email)->delete();

        $user = User::create([
            'employee_id' => 'AMAN001',
            'full_name' => 'Aman Shah',
            'phone' => $phone,
            'email' => $email,
            'country_base' => 'UAE',
            'airline_id' => 1,
            'plane_type_id' => 1,
            'position_id' => 1,
            'password' => Hash::make('12345678'),
            'phone_verified_at' => now(), // Already verified
            'email_verified_at' => now(),
        ]);

        $user->assignRole('flight_attendant');
        
        $this->info('✅ Test user created successfully!');
        $this->line("");
        $this->line("Credentials:");
        $this->table(
            ['Field', 'Value'],
            [
                ['Email', $email],
                ['Password', '12345678'],
                ['User ID', $user->id],
                ['Role', 'flight_attendant'],
            ]
        );
        $this->line("");
        $this->info("Use these credentials in your API tests!");
    }
}
