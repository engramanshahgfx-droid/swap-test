<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            if (!Schema::hasColumn('messages', 'mentioned_trip_id')) {
                $table->unsignedBigInteger('mentioned_trip_id')->nullable()->after('message_type');
                $table->foreign('mentioned_trip_id')->references('id')->on('published_trips')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            if (Schema::hasColumn('messages', 'mentioned_trip_id')) {
                $table->dropForeign(['mentioned_trip_id']);
                $table->dropColumn('mentioned_trip_id');
            }
        });
    }
};
