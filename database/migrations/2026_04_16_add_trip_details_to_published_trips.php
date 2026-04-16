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
        Schema::table('published_trips', function (Blueprint $table) {
            // Add separate fields for trip details (if they don't exist)
            if (!Schema::hasColumn('published_trips', 'flight_number')) {
                $table->string('flight_number')->nullable()->after('flight_id');
            }
            
            if (!Schema::hasColumn('published_trips', 'legs')) {
                $table->integer('legs')->nullable()->after('flight_number');
            }
            
            if (!Schema::hasColumn('published_trips', 'fly_type')) {
                $table->string('fly_type')->nullable()->after('legs');
            }
            
            if (!Schema::hasColumn('published_trips', 'report_time')) {
                $table->string('report_time')->nullable()->after('fly_type');
            }
            
            if (!Schema::hasColumn('published_trips', 'offer_lo')) {
                $table->string('offer_lo')->nullable()->after('report_time');
            }
            
            if (!Schema::hasColumn('published_trips', 'ask_lo')) {
                $table->string('ask_lo')->nullable()->after('offer_lo');
            }
            
            if (!Schema::hasColumn('published_trips', 'details')) {
                $table->text('details')->nullable()->after('ask_lo');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('published_trips', function (Blueprint $table) {
            if (Schema::hasColumn('published_trips', 'flight_number')) {
                $table->dropColumn('flight_number');
            }
            if (Schema::hasColumn('published_trips', 'legs')) {
                $table->dropColumn('legs');
            }
            if (Schema::hasColumn('published_trips', 'fly_type')) {
                $table->dropColumn('fly_type');
            }
            if (Schema::hasColumn('published_trips', 'report_time')) {
                $table->dropColumn('report_time');
            }
            if (Schema::hasColumn('published_trips', 'offer_lo')) {
                $table->dropColumn('offer_lo');
            }
            if (Schema::hasColumn('published_trips', 'ask_lo')) {
                $table->dropColumn('ask_lo');
            }
            if (Schema::hasColumn('published_trips', 'details')) {
                $table->dropColumn('details');
            }
        });
    }
};
