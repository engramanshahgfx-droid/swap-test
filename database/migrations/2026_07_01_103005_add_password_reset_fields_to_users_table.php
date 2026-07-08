<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('reset_password_token')->nullable();
            $table->timestamp('reset_password_expires_at')->nullable();
            $table->timestamp('password_changed_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'reset_password_token',
                'reset_password_expires_at',
                'password_changed_at'
            ]);
        });
    }
};
