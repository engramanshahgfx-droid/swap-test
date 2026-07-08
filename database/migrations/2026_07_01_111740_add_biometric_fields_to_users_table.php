<?php
// database/migrations/xxxx_add_biometric_fields_to_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('four_digit_code')->nullable();
            $table->boolean('face_id_enabled')->default(false);
            $table->string('biometric_public_key')->nullable();
            $table->string('device_id')->nullable();
            $table->boolean('biometric_login_enabled')->default(false);
            $table->timestamp('biometric_setup_at')->nullable();
            $table->integer('biometric_login_attempts')->default(0);
            $table->timestamp('biometric_locked_until')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'four_digit_code',
                'face_id_enabled',
                'biometric_public_key',
                'device_id',
                'biometric_login_enabled',
                'biometric_setup_at',
                'biometric_login_attempts',
                'biometric_locked_until'
            ]);
        });
    }
};
