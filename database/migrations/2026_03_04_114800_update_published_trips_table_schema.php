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

        if (Schema::hasColumn('published_trips', 'flight_id')) {
            try {
                Schema::table('published_trips', function (Blueprint $table) {
                    $table->dropForeign(['flight_id']);
                });
            } catch (\Throwable $e) {
                // Ignore when the foreign key is already absent or the driver does not support it.
            }
        }

        if (Schema::hasColumn('published_trips', 'user_id')) {
            try {
                Schema::table('published_trips', function (Blueprint $table) {
                    $table->dropForeign(['user_id']);
                });
            } catch (\Throwable $e) {
                // Ignore when the foreign key is already absent or the driver does not support it.
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('published_trips', 'flight_id')) {
            try {
                Schema::table('published_trips', function (Blueprint $table) {
                    $table->dropForeign(['flight_id']);
                });
            } catch (\Throwable $e) {
                // Ignore when the foreign key is already absent or the driver does not support it.
            }

            Schema::table('published_trips', function (Blueprint $table) {
                $table->dropColumn('flight_id');
            });
        }

        if (Schema::hasColumn('published_trips', 'user_id')) {
            try {
                Schema::table('published_trips', function (Blueprint $table) {
                    $table->dropForeign(['user_id']);
                });
            } catch (\Throwable $e) {
                // Ignore when the foreign key is already absent or the driver does not support it.
            }

            Schema::table('published_trips', function (Blueprint $table) {
                $table->dropColumn('user_id');
            });
        }
    }
};
