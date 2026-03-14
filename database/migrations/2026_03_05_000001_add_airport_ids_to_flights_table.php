<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('flights', function (Blueprint $table) {
            $table->foreignId('departure_airport_id')->nullable()->after('arrival_airport')->constrained('airports')->nullOnDelete();
            $table->foreignId('arrival_airport_id')->nullable()->after('departure_airport_id')->constrained('airports')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('flights', function (Blueprint $table) {
            $table->dropForeign(['departure_airport_id']);
            $table->dropForeign(['arrival_airport_id']);
            $table->dropColumn(['departure_airport_id', 'arrival_airport_id']);
        });
    }
};
