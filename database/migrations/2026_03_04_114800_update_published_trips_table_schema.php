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
            // Add flight_id and user_id if they don't exist
            if (!Schema::hasColumn('published_trips', 'flight_id')) {
                $table->foreignId('flight_id')->nullable()->constrained()->onDelete('cascade');
            }
            if (!Schema::hasColumn('published_trips', 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            }
            // Rename status enum values if needed
            // We'll drop and recreate the status column with broader values
            if (Schema::hasColumn('published_trips', 'status')) {
                $table->dropColumn('status');
            }
            $table->enum('status', ['available', 'active', 'closed', 'expired'])->default('available')->after('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('published_trips', function (Blueprint $table) {
            if (Schema::hasColumn('published_trips', 'flight_id')) {
                $table->dropForeignIdFor(\App\Models\Flight::class);
                $table->dropColumn('flight_id');
            }
            if (Schema::hasColumn('published_trips', 'user_id')) {
                $table->dropForeignIdFor(\App\Models\User::class);
                $table->dropColumn('user_id');
            }
        });
    }
};
