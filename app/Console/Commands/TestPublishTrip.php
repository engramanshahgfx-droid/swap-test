<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Flight;

class TestPublishTrip extends Command
{
    protected $signature = 'test:publish-trip';

    public function handle()
    {
        $this->info('Testing firstOrCreate with flight data...');
        
        try {
            $date = Carbon::parse('2026-03-10');
            $this->info("Date parsed: " . $date->format('Y-m-d'));
            
            $flight = Flight::firstOrCreate(
                [
                    'flight_number' => 'TEST789',
                    'departure_airport' => 'RUH', 
                    'arrival_airport' => 'JED',
                ],
                [
                    'departure_date' => $date,
                    'departure_time' => '08:00:00',
                    'arrival_time' => '10:30:00',
                    'airline_id' => 1,
                    'plane_type_id' => 1,
                    'status' => 'scheduled',
                ]
            );
            
            $this->info("Flight created/found with ID: " . $flight->id);
            $this->info("Flight number: " . $flight->flight_number);
            
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            $this->error("File: " . $e->getFile());
            $this->error("Line: " . $e->getLine());
        }
    }
}
