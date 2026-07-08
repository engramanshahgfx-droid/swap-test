<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'willing_fly_days')) {
                $table->json('willing_fly_days')->nullable()->after('allow_messages_from');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'willing_fly_days')) {
                $table->dropColumn('willing_fly_days');
            }
        });
    }
};
