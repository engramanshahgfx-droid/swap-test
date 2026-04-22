<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('flights', function (Blueprint $table) {
            if (!Schema::hasColumn('flights', 'arrival_date')) {
                $table->date('arrival_date')->nullable()->after('departure_date');
            }
        });

        if (Schema::hasColumn('flights', 'arrival_date') && Schema::hasColumn('flights', 'departure_date')) {
            DB::statement('UPDATE flights SET arrival_date = departure_date WHERE arrival_date IS NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('flights', function (Blueprint $table) {
            if (Schema::hasColumn('flights', 'arrival_date')) {
                $table->dropColumn('arrival_date');
            }
        });
    }
};
