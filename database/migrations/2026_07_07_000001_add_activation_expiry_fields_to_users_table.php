<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            $columns = Schema::getConnection()->getSchemaBuilder()->getColumnListing('users');
            if (in_array('status_new', $columns, true)) {
                DB::statement('DROP TABLE users');
            }
        }

        if ($driver === 'sqlite') {
            Schema::table('users', function (Blueprint $table) {
                $table->string('status_new')->nullable();
            });
            DB::statement('UPDATE users SET status_new = status');
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('status');
            });
            Schema::table('users', function (Blueprint $table) {
                $table->string('status')->default('inactive');
            });
            DB::statement('UPDATE users SET status = COALESCE(status_new, "inactive")');
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('status_new');
            });
        } else {
            DB::statement("ALTER TABLE users MODIFY COLUMN status ENUM('active','inactive','blocked','expired','suspended','permanent') DEFAULT 'inactive'");
        }

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'activation_start_date')) {
                $table->date('activation_start_date')->nullable()->after('status');
            }

            if (!Schema::hasColumn('users', 'activation_end_date')) {
                $table->date('activation_end_date')->nullable()->after('activation_start_date');
            }

            if (!Schema::hasColumn('users', 'activation_type')) {
                $table->string('activation_type')->default('temporary')->after('activation_end_date');
            }

            if (!Schema::hasColumn('users', 'is_permanent')) {
                $table->boolean('is_permanent')->default(false)->after('activation_type');
            }

            if (!Schema::hasColumn('users', 'grace_period_days')) {
                $table->unsignedInteger('grace_period_days')->default(7)->after('is_permanent');
            }

            if (!Schema::hasColumn('users', 'grace_period_end_date')) {
                $table->date('grace_period_end_date')->nullable()->after('grace_period_days');
            }

            if (!Schema::hasColumn('users', 'last_expiry_notification_at')) {
                $table->timestamp('last_expiry_notification_at')->nullable()->after('grace_period_end_date');
            }

            if (!Schema::hasColumn('users', 'last_expiry_notification_stage')) {
                $table->string('last_expiry_notification_stage')->nullable()->after('last_expiry_notification_at');
            }

            if (!Schema::hasColumn('users', 'activation_notes')) {
                $table->text('activation_notes')->nullable()->after('last_expiry_notification_stage');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'activation_notes')) {
                $table->dropColumn('activation_notes');
            }
            if (Schema::hasColumn('users', 'last_expiry_notification_stage')) {
                $table->dropColumn('last_expiry_notification_stage');
            }
            if (Schema::hasColumn('users', 'last_expiry_notification_at')) {
                $table->dropColumn('last_expiry_notification_at');
            }
            if (Schema::hasColumn('users', 'grace_period_end_date')) {
                $table->dropColumn('grace_period_end_date');
            }
            if (Schema::hasColumn('users', 'grace_period_days')) {
                $table->dropColumn('grace_period_days');
            }
            if (Schema::hasColumn('users', 'is_permanent')) {
                $table->dropColumn('is_permanent');
            }
            if (Schema::hasColumn('users', 'activation_type')) {
                $table->dropColumn('activation_type');
            }
            if (Schema::hasColumn('users', 'activation_end_date')) {
                $table->dropColumn('activation_end_date');
            }
            if (Schema::hasColumn('users', 'activation_start_date')) {
                $table->dropColumn('activation_start_date');
            }
        });
    }
};
