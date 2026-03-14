<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Use raw SQL to modify columns safely
        DB::statement('ALTER TABLE users MODIFY airline_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE users MODIFY plane_type_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE users MODIFY position_id BIGINT UNSIGNED NULL');
    }

    public function down(): void
    {
        // No down - this is just making columns nullable
    }
};
