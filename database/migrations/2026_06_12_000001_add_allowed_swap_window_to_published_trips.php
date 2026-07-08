<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('published_trips', 'allowed_swap_window')) {
            Schema::table('published_trips', function (Blueprint $table) {
                $table->enum('allowed_swap_window', ['same_day', 'day_before', 'any'])->default('same_day')->after('expires_at');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('published_trips', 'allowed_swap_window')) {
            Schema::table('published_trips', function (Blueprint $table) {
                $table->dropColumn('allowed_swap_window');
            });
        }
    }
};
