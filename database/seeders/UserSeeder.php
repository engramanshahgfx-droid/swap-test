<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Airline;
use App\Models\PlaneType;
use App\Models\Position;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $ethiopian = Airline::where('code', 'ET')->first();
        $planeType = PlaneType::where('airline_id', $ethiopian->id)->first();
        $adminPosition = Position::where('slug', 'admin')->first();
        $managerPosition = Position::where('slug', 'crew_manager')->first();
        $purserPosition = Position::where('slug', 'purser')->first();
        $faPosition = Position::where('slug', 'flight_attendant')->first();

        // Create Super Admin
        $admin = User::firstOrCreate(['email' => 'admin@crewswap.com'], [
            'employee_id' => 'ADMIN001',
            'full_name' => 'System Admin',
            'phone' => '+251911000001',
            'email' => 'admin@crewswap.com',
            'country_base' => 'Ethiopia',
            'airline_id' => $ethiopian->id,
            'plane_type_id' => $planeType->id,
            'position_id' => $adminPosition->id,
            'password' => Hash::make('password'),
            'status' => 'active',
            'phone_verified_at' => now(),
            'email_verified_at' => now(),
        ]);
        $admin->assignRole('super-admin');

        // Create Crew Manager
        $manager = User::firstOrCreate(['email' => 'manager@crewswap.com'], [
            'employee_id' => 'MGR001',
            'full_name' => 'John Manager',
            'phone' => '+251911000002',
            'email' => 'manager@crewswap.com',
            'country_base' => 'Ethiopia',
            'airline_id' => $ethiopian->id,
            'plane_type_id' => $planeType->id,
            'position_id' => $managerPosition->id,
            'password' => Hash::make('password'),
            'status' => 'active',
            'phone_verified_at' => now(),
            'email_verified_at' => now(),
        ]);
        $manager->assignRole('crew_manager');

        // Create Purser
        $purser = User::firstOrCreate(['email' => 'purser@crewswap.com'], [
            'employee_id' => 'PUR001',
            'full_name' => 'Sarah Purser',
            'phone' => '+251911000003',
            'email' => 'purser@crewswap.com',
            'country_base' => 'Ethiopia',
            'airline_id' => $ethiopian->id,
            'plane_type_id' => $planeType->id,
            'position_id' => $purserPosition->id,
            'password' => Hash::make('password'),
            'status' => 'active',
            'phone_verified_at' => now(),
            'email_verified_at' => now(),
        ]);
        $purser->assignRole('purser');

        // Create Flight Attendants
        $flightAttendants = [
            [
                'employee_id' => 'FA001',
                'full_name' => 'Alice Flight',
                'phone' => '+251911000004',
                'email' => 'alice@crewswap.com',
            ],
            [
                'employee_id' => 'FA002',
                'full_name' => 'Bob Attendant',
                'phone' => '+251911000005',
                'email' => 'bob@crewswap.com',
            ],
            [
                'employee_id' => 'FA003',
                'full_name' => 'Carol Crew',
                'phone' => '+251911000006',
                'email' => 'carol@crewswap.com',
            ],
        ];

        foreach ($flightAttendants as $faData) {
            $fa = User::firstOrCreate(['email' => $faData['email']], [
                'employee_id' => $faData['employee_id'],
                'full_name' => $faData['full_name'],
                'phone' => $faData['phone'],
                'email' => $faData['email'],
                'country_base' => 'Ethiopia',
                'airline_id' => $ethiopian->id,
                'plane_type_id' => $planeType->id,
                'position_id' => $faPosition->id,
                'password' => Hash::make('password'),
                'status' => 'active',
                'phone_verified_at' => now(),
                'email_verified_at' => now(),
            ]);
            $fa->assignRole('flight_attendant');
        }
    }
}
