<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'hide_employee_id')) {
                $table->boolean('hide_employee_id')->default(false)->after('password');
            }
            if (!Schema::hasColumn('users', 'show_online_status')) {
                $table->boolean('show_online_status')->default(true)->after('hide_employee_id');
            }
            if (!Schema::hasColumn('users', 'allow_messages_from')) {
                $table->string('allow_messages_from', 32)->default('everyone')->after('show_online_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'allow_messages_from')) {
                $table->dropColumn('allow_messages_from');
            }
            if (Schema::hasColumn('users', 'show_online_status')) {
                $table->dropColumn('show_online_status');
            }
            if (Schema::hasColumn('users', 'hide_employee_id')) {
                $table->dropColumn('hide_employee_id');
            }
        });
    }
};
