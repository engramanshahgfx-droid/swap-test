<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('published_trips', function (Blueprint $table) {
            if (!Schema::hasColumn('published_trips', 'image_path')) {
                $table->string('image_path')->nullable()->after('details');
            }
        });
    }

    public function down(): void
    {
        Schema::table('published_trips', function (Blueprint $table) {
            if (Schema::hasColumn('published_trips', 'image_path')) {
                $table->dropColumn('image_path');
            }
        });
    }
};
