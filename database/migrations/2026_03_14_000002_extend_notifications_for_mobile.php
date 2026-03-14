<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            if (!Schema::hasColumn('notifications', 'title')) {
                $table->string('title')->nullable()->after('user_id');
            }

            if (!Schema::hasColumn('notifications', 'message')) {
                $table->text('message')->nullable()->after('title');
            }

            if (!Schema::hasColumn('notifications', 'sound')) {
                $table->string('sound')->nullable()->after('type');
            }

            if (!Schema::hasColumn('notifications', 'is_read')) {
                $table->boolean('is_read')->default(false)->after('sound');
            }

            if (!Schema::hasColumn('notifications', 'payload')) {
                $table->json('payload')->nullable()->after('is_read');
            }
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            if (Schema::hasColumn('notifications', 'payload')) {
                $table->dropColumn('payload');
            }

            if (Schema::hasColumn('notifications', 'is_read')) {
                $table->dropColumn('is_read');
            }

            if (Schema::hasColumn('notifications', 'sound')) {
                $table->dropColumn('sound');
            }

            if (Schema::hasColumn('notifications', 'message')) {
                $table->dropColumn('message');
            }

            if (Schema::hasColumn('notifications', 'title')) {
                $table->dropColumn('title');
            }
        });
    }
};
