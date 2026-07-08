<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('published_trips', function (Blueprint $table) {
            if (!Schema::hasColumn('published_trips', 'is_urgent')) {
                $table->boolean('is_urgent')->default(false)->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('published_trips', function (Blueprint $table) {
            if (Schema::hasColumn('published_trips', 'is_urgent')) {
                $table->dropColumn('is_urgent');
            }
        });
    }
};
