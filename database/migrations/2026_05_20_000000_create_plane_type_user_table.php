<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('plane_type_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('plane_type_id')->constrained('plane_types')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['user_id', 'plane_type_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('plane_type_user');
    }
};
