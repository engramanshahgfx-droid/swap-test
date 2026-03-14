<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Position;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        $positions = [
            // Pilots & Flight Crew
            ['name' => 'Captain', 'slug' => 'captain', 'description' => 'Aircraft captain responsible for flight operations and crew', 'level' => 5],
            ['name' => 'First Officer', 'slug' => 'first_officer', 'description' => 'Co-pilot assisting the captain with flight operations', 'level' => 4],
            ['name' => 'Flight Engineer', 'slug' => 'flight_engineer', 'description' => 'Engineer responsible for aircraft systems and maintenance checks', 'level' => 3],
            ['name' => 'Flight Mechanic', 'slug' => 'flight_mechanic', 'description' => 'Technician responsible for aircraft mechanical systems', 'level' => 2],

            // Cabin Crew Management
            ['name' => 'Cabin Manager', 'slug' => 'cabin_manager', 'description' => 'Senior cabin crew manager responsible for cabin team leadership', 'level' => 3],
            ['name' => 'Purser', 'slug' => 'purser', 'description' => 'Senior cabin crew member responsible for leading the cabin team', 'level' => 3],
            ['name' => 'Senior Flight Attendant', 'slug' => 'senior_flight_attendant', 'description' => 'Experienced cabin crew member with leadership responsibilities', 'level' => 2],
            ['name' => 'Premium Flight Attendant', 'slug' => 'premium_flight_attendant', 'description' => 'Cabin crew member specializing in premium cabin service', 'level' => 2],
            ['name' => 'Flight Attendant', 'slug' => 'flight_attendant', 'description' => 'Cabin crew member responsible for passenger safety and comfort', 'level' => 1],

            // Cabin Service
            ['name' => 'Butler', 'slug' => 'butler', 'description' => 'Premium cabin service specialist providing butler service', 'level' => 1],
            ['name' => 'Chef', 'slug' => 'chef', 'description' => 'Galley chef responsible for meal preparation and service', 'level' => 1],
            ['name' => 'Cabin Observer', 'slug' => 'cabin_observer', 'description' => 'Observer monitoring cabin operations and crew performance', 'level' => 1],

            // Security
            ['name' => 'Air Marshal', 'slug' => 'air_marshal', 'description' => 'Security officer responsible for flight security operations', 'level' => 3],
            ['name' => 'Security Officer', 'slug' => 'security_officer', 'description' => 'Security personnel assisting with onboard security', 'level' => 2],
        ];

        foreach ($positions as $position) {
            Position::firstOrCreate(['slug' => $position['slug']], $position);
        }
    }
}
