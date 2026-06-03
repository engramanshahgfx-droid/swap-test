<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('flights', function (Blueprint $table) {
            $table->dropUnique(['flight_number']);
        });

        Schema::table('flights', function (Blueprint $table) {
            $table->unique([
                'flight_number',
                'departure_date',
                'departure_airport',
                'arrival_airport',
            ], 'flights_flight_number_departure_date_arrival_unique');
        });
    }

    public function down(): void
    {
        Schema::table('flights', function (Blueprint $table) {
            $table->dropUnique('flights_flight_number_departure_date_arrival_unique');
        });

        Schema::table('flights', function (Blueprint $table) {
            $table->unique('flight_number');
        });
    }
};
