<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Flight;
use App\Models\Airline;
use App\Models\PlaneType;
use Carbon\Carbon;

class FlightSeeder extends Seeder
{
    public function run(): void
    {
        $ethiopian = Airline::where('code', 'ET')->first();
        $planeTypes = PlaneType::where('airline_id', $ethiopian->id)->get();

        $flights = [
            [
                'flight_number' => 'ET302',
                'departure_airport' => 'ADD',
                'arrival_airport' => 'LHR',
                'departure_date' => Carbon::now()->addDays(3)->toDateString(),
                'departure_time' => '08:00',
                'arrival_time' => '16:00',
            ],
            [
                'flight_number' => 'ET500',
                'departure_airport' => 'ADD',
                'arrival_airport' => 'JFK',
                'departure_date' => Carbon::now()->addDays(5)->toDateString(),
                'departure_time' => '22:00',
                'arrival_time' => '12:00',
            ],
            [
                'flight_number' => 'ET312',
                'departure_airport' => 'ADD',
                'arrival_airport' => 'NBO',
                'departure_date' => Carbon::now()->addDays(2)->toDateString(),
                'departure_time' => '10:30',
                'arrival_time' => '12:30',
            ],
            [
                'flight_number' => 'ET150',
                'departure_airport' => 'ADD',
                'arrival_airport' => 'DXB',
                'departure_date' => Carbon::now()->addDays(4)->toDateString(),
                'departure_time' => '14:15',
                'arrival_time' => '18:15',
            ],
            [
                'flight_number' => 'ET100',
                'departure_airport' => 'ADD',
                'arrival_airport' => 'DIR',
                'departure_date' => Carbon::now()->addDays(1)->toDateString(),
                'departure_time' => '06:00',
                'arrival_time' => '07:00',
            ],
            [
                'flight_number' => 'ET102',
                'departure_airport' => 'ADD',
                'arrival_airport' => 'MQX',
                'departure_date' => Carbon::now()->addDays(1)->toDateString(),
                'departure_time' => '11:00',
                'arrival_time' => '12:15',
            ],
            [
                'flight_number' => 'ET400',
                'departure_airport' => 'ADD',
                'arrival_airport' => 'CDG',
                'departure_date' => Carbon::now()->addDays(6)->toDateString(),
                'departure_time' => '23:30',
                'arrival_time' => '08:00',
            ],
            [
                'flight_number' => 'ET600',
                'departure_airport' => 'ADD',
                'arrival_airport' => 'PVG',
                'departure_date' => Carbon::now()->addDays(7)->toDateString(),
                'departure_time' => '23:45',
                'arrival_time' => '09:45',
            ],
        ];

        foreach ($flights as $flightData) {
            Flight::firstOrCreate(['flight_number' => $flightData['flight_number']], [
                'flight_number' => $flightData['flight_number'],
                'departure_airport' => $flightData['departure_airport'],
                'arrival_airport' => $flightData['arrival_airport'],
                'departure_date' => $flightData['departure_date'],
                'departure_time' => $flightData['departure_time'],
                'arrival_time' => $flightData['arrival_time'],
                'airline_id' => $ethiopian->id,
                'plane_type_id' => $planeTypes->random()->id,
                'status' => 'scheduled',
            ]);
        }
    }
}
