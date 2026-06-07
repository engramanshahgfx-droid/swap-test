<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Remove the unique constraint on user_id and flight_id to allow multiple trips for the same flight
     */
    public function up(): void
    {
        // Use raw SQL to drop the unique constraint since it's tied to foreign keys
        DB::statement('ALTER TABLE `user_trips` DROP INDEX `user_trips_user_id_flight_id_unique`');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_trips', function (Blueprint $table) {
            $table->unique(['user_id', 'flight_id']);
        });
    }
};
