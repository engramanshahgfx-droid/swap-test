<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            PositionSeeder::class,
            AirlineSeeder::class,
            AirportSeeder::class,
            PlaneTypeSeeder::class,
            UserSeeder::class,
            FlightSeeder::class,
        ]);
    }
}
