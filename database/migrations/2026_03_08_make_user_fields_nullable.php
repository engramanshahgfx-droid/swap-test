<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Check database type
        $driver = DB::getDriverName();
        
        if ($driver === 'mysql') {
            // MySQL: Use raw statements to modify columns
            DB::statement('ALTER TABLE users MODIFY airline_id BIGINT UNSIGNED NULL');
            DB::statement('ALTER TABLE users MODIFY plane_type_id BIGINT UNSIGNED NULL');
            DB::statement('ALTER TABLE users MODIFY position_id BIGINT UNSIGNED NULL');
        }
        // SQLite doesn't need modification - columns are already created nullable
    }

    public function down(): void
    {
        // No down - this is just making columns nullable
    }
};
