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
        // Ensure single-column indexes exist so foreign keys remain satisfied,
        // then drop the composite unique index that prevented duplicate trips.
        Schema::table('user_trips', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('flight_id');
        });

        // Drop the unique constraint (use explicit name to be safe)
        Schema::table('user_trips', function (Blueprint $table) {
            $table->dropUnique('user_trips_user_id_flight_id_unique');
        });
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
