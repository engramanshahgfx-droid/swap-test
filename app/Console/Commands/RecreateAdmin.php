<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Airline;
use App\Models\PlaneType;
use App\Models\Position;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class RecreateAdmin extends Command
{
    protected $signature = 'admin:recreate {password=password}';
    protected $description = 'Recreate admin user';

    public function handle()
    {
        // Delete existing admin
        User::where('email', 'admin@crewswap.com')->delete();

        // Get related models
        $airline = Airline::first();
        $planeType = PlaneType::first();
        $position = Position::first();

        // Create new admin
        $admin = User::create([
            'employee_id' => 'ADMIN001',
            'full_name' => 'System Admin',
            'phone' => '+251911000001',
            'email' => 'admin@crewswap.com',
            'country_base' => 'Ethiopia',
            'airline_id' => $airline->id,
            'plane_type_id' => $planeType->id,
            'position_id' => $position->id,
            'password' => Hash::make($this->argument('password')),
            'status' => 'active',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);

        $admin->assignRole('super-admin');

        $password = $this->argument('password');
        $this->info("✓ Admin user recreated");
        $this->info("  Email: admin@crewswap.com");
        $this->info("  Password: {$password}");
        $this->info("  Role: super-admin");
    }
}
