<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('published_trips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_trip_id')->constrained()->onDelete('cascade');
            $table->text('reason')->nullable();
            $table->dateTime('published_at');
            $table->dateTime('expires_at')->nullable();
            $table->enum('status', ['active', 'closed', 'expired'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('published_trips');
    }
};
