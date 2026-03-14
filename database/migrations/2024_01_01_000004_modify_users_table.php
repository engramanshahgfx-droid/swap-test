<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Rename name to full_name
            $table->renameColumn('name', 'full_name');
            
            $table->string('employee_id')->unique()->after('id');
            $table->string('phone')->unique()->after('email');
            $table->string('country_base')->after('phone');
            $table->unsignedBigInteger('airline_id')->after('country_base');
            $table->unsignedBigInteger('plane_type_id')->after('airline_id');
            $table->unsignedBigInteger('position_id')->after('plane_type_id');
            $table->enum('status', ['active', 'inactive', 'blocked'])->default('active')->after('position_id');
            $table->timestamp('phone_verified_at')->nullable()->after('email_verified_at');
            $table->string('otp_code')->nullable()->after('phone_verified_at');
            $table->timestamp('otp_expires_at')->nullable()->after('otp_code');
            
            $table->foreign('airline_id')->references('id')->on('airlines')->onDelete('cascade');
            $table->foreign('plane_type_id')->references('id')->on('plane_types')->onDelete('cascade');
            $table->foreign('position_id')->references('id')->on('positions')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['airline_id']);
            $table->dropForeign(['plane_type_id']);
            $table->dropForeign(['position_id']);
            $table->dropColumn(['employee_id', 'phone', 'country_base', 'airline_id', 'plane_type_id', 'position_id', 'status', 'phone_verified_at', 'otp_code', 'otp_expires_at']);
            $table->renameColumn('full_name', 'name');
        });
    }
};
