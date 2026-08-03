<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('user_trips', function (Blueprint $table) {
            if (!Schema::hasColumn('user_trips', 'user_roster_id')) {
                $table->foreignId('user_roster_id')->nullable()->constrained('user_rosters')->onDelete('set null');
            }
            if (!Schema::hasColumn('user_trips', 'pairing_number')) {
                $table->string('pairing_number')->nullable();
            }
            if (!Schema::hasColumn('user_trips', 'duty_type')) {
                $table->string('duty_type')->nullable(); // flight, layover, rest, positioning, deadhead
            }
            if (!Schema::hasColumn('user_trips', 'report_time')) {
                $table->string('report_time')->nullable();
            }
            if (!Schema::hasColumn('user_trips', 'release_time')) {
                $table->string('release_time')->nullable();
            }
            if (!Schema::hasColumn('user_trips', 'layover_location')) {
                $table->string('layover_location')->nullable();
            }
            if (!Schema::hasColumn('user_trips', 'layover_duration')) {
                $table->string('layover_duration')->nullable();
            }
            if (!Schema::hasColumn('user_trips', 'legs')) {
                $table->json('legs')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_trips', function (Blueprint $table) {
            $table->dropForeign(['user_roster_id']);
            $table->dropColumn([
                'user_roster_id',
                'pairing_number',
                'duty_type',
                'report_time',
                'release_time',
                'layover_location',
                'layover_duration',
                'legs',
            ]);
        });
    }
};
