<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('swap_requests', function (Blueprint $table) {
            $table->string('manager_approval_status')->default('pending')->after('status');
            $table->text('manager_notes')->nullable()->after('manager_approval_status');
            $table->foreignId('manager_approved_by_id')->nullable()->after('manager_notes')->constrained('users')->nullOnDelete();
            $table->timestamp('manager_approved_at')->nullable()->after('manager_approved_by_id');
        });

        DB::statement("ALTER TABLE swap_requests MODIFY status ENUM('pending', 'approved', 'rejected', 'approved_by_owner', 'rejected_by_owner', 'manager_approved', 'manager_rejected', 'completed', 'cancelled') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE swap_requests MODIFY status ENUM('pending', 'approved', 'rejected', 'completed') NOT NULL DEFAULT 'pending'");

        Schema::table('swap_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('manager_approved_by_id');
            $table->dropColumn(['manager_approval_status', 'manager_notes', 'manager_approved_at']);
        });
    }
};