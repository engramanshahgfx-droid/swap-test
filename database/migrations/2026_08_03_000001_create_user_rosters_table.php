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
        Schema::create('user_rosters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('month')->nullable();
            $table->integer('year')->nullable();
            $table->string('line_number')->nullable();
            $table->string('position')->nullable();
            $table->string('file_name')->nullable();
            $table->string('file_path')->nullable();
            $table->integer('total_duties')->default(0);
            $table->string('total_flight_hours')->nullable();
            $table->string('total_credit_hours')->nullable();
            $table->longText('raw_text')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_rosters');
    }
};
