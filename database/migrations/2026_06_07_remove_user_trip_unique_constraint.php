<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Remove the unique constraint on user_id and flight_id to allow multiple trips for the same flight
     */
    public function up(): void
    {
        Schema::table('user_trips', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'flight_id']);
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
