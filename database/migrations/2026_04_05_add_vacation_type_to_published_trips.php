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
            // Add vacation_type if it doesn't exist
            if (!Schema::hasColumn('published_trips', 'vacation_type')) {
                $table->string('vacation_type')->nullable()->default('trip')->after('status');
            }
            
            // Add metadata JSON column if it doesn't exist
            if (!Schema::hasColumn('published_trips', 'metadata')) {
                $table->json('metadata')->nullable()->after('vacation_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('published_trips', function (Blueprint $table) {
            if (Schema::hasColumn('published_trips', 'vacation_type')) {
                $table->dropColumn('vacation_type');
            }
            if (Schema::hasColumn('published_trips', 'metadata')) {
                $table->dropColumn('metadata');
            }
        });
    }
};
