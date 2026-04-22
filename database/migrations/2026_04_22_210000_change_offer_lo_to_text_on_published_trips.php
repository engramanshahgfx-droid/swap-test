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
            if (Schema::hasColumn('published_trips', 'offer_lo')) {
                $table->text('offer_lo')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('published_trips', function (Blueprint $table) {
            if (Schema::hasColumn('published_trips', 'offer_lo')) {
                $table->string('offer_lo')->nullable()->change();
            }
        });
    }
};
