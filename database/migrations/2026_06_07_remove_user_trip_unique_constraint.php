<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Remove the unique constraint on user_id and flight_id to allow multiple trips for the same flight
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            $indexes = DB::select('SHOW INDEX FROM user_trips');
            $indexNames = array_map(fn ($row) => $row->Key_name, $indexes);

            if (!in_array('user_trips_user_id_index', $indexNames, true)) {
                Schema::table('user_trips', function (Blueprint $table) {
                    $table->index('user_id');
                });
            }

            if (!in_array('user_trips_flight_id_index', $indexNames, true)) {
                Schema::table('user_trips', function (Blueprint $table) {
                    $table->index('flight_id');
                });
            }

            try {
                Schema::table('user_trips', function (Blueprint $table) {
                    $table->dropUnique('user_trips_user_id_flight_id_unique');
                });
            } catch (\Throwable $e) {
                // Ignore when the unique index is already absent.
            }
        }
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
