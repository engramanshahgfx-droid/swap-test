<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PlaneType;
use App\Models\Airline;

class PlaneTypeSeeder extends Seeder
{
    public function run(): void
    {
        $ethiopianId = Airline::where('code', 'ET')->first()->id;
        $kenyaId = Airline::where('code', 'KQ')->first()->id;
        $saId = Airline::where('code', 'SA')->first()->id;

        $planeTypes = [
            // Ethiopian Airlines
            [
                'name' => 'Boeing 787-8 Dreamliner',
                'code' => 'B788',
                'airline_id' => $ethiopianId,
                'capacity' => 270,
                'is_active' => true,
            ],
            [
                'name' => 'Boeing 777-300ER',
                'code' => 'B77W',
                'airline_id' => $ethiopianId,
                'capacity' => 400,
                'is_active' => true,
            ],
            [
                'name' => 'Airbus A350-900',
                'code' => 'A359',
                'airline_id' => $ethiopianId,
                'capacity' => 315,
                'is_active' => true,
            ],
            // Kenya Airways
            [
                'name' => 'Boeing 787-8',
                'code' => 'B788-KQ',
                'airline_id' => $kenyaId,
                'capacity' => 234,
                'is_active' => true,
            ],
            [
                'name' => 'Boeing 737-800',
                'code' => 'B738-KQ',
                'airline_id' => $kenyaId,
                'capacity' => 162,
                'is_active' => true,
            ],
            // South African Airways
            [
                'name' => 'Airbus A330-300',
                'code' => 'A333-SA',
                'airline_id' => $saId,
                'capacity' => 277,
                'is_active' => true,
            ],
            [
                'name' => 'Airbus A320-200',
                'code' => 'A320-SA',
                'airline_id' => $saId,
                'capacity' => 150,
                'is_active' => true,
            ],
        ];

        foreach ($planeTypes as $planeType) {
            PlaneType::firstOrCreate(['code' => $planeType['code']], $planeType);
        }
    }
}
