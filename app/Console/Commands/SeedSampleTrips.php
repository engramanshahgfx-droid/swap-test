<?php

namespace App\Console\Commands;

use App\Models\UserTrip;
use App\Models\PublishedTrip;
use App\Models\Flight;
use Illuminate\Console\Command;

class SeedSampleTrips extends Command
{
    protected $signature = 'seed:sample-trips';
    protected $description = 'Seed sample trips for testing API';

    public function handle()
    {
        $this->info('=== Seeding Sample Trips ===');

        // Get the test user
        $user = \App\Models\User::where('email', 'aman@test.com')->first();
        if (!$user) {
            $this->error('Test user aman@test.com not found!');
            return;
        }

        // Get or create flights
        $flights = Flight::take(3)->get();
        if ($flights->isEmpty()) {
            $this->warn('No flights in database. Creating sample flights...');
            for ($i = 1; $i <= 3; $i++) {
                Flight::create([
                    'airline_id' => 1,
                    'flight_number' => 'SV' . (100 + $i),
                    'departure_airport' => 'RUH',
                    'arrival_airport' => 'JED',
                    'date' => now()->addDays($i),
                    'time' => now()->addHours($i * 2),
                    'duration_minutes' => 120,
                    'trip_type' => 'Round Trip',
                ]);
            }
            $flights = Flight::take(3)->get();
        }

        // Create user trips
        foreach ($flights as $i => $flight) {
            UserTrip::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'flight_id' => $flight->id,
                ],
                [
                    'status' => ['assigned', 'completed', 'cancelled'][$i % 3],
                    'role' => ['Captain', 'First Officer', 'Flight Attendant'][$i % 3],
                    'notes' => 'Sample trip for testing API',
                ]
            );
        }

        $this->info("✓ Created " . count($flights) . " sample trips for user {$user->email}");
        $this->info("\nYou can now test:");
        $this->line("  - GET /api/my-trips");
        $this->line("  - GET /api/trip-details/{1..3}");
        $this->line("  - GET /api/browse-trips");
    }
}
