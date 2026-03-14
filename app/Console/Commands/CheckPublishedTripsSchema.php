<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckPublishedTripsSchema extends Command
{
    protected $signature = 'check:published-trips-schema';

    public function handle()
    {
        $columns = DB::select('DESCRIBE published_trips');
        foreach ($columns as $col) {
            $this->info("Column: {$col->Field}, Type: {$col->Type}, Null: {$col->Null}, Default: {$col->Default}");
        }
    }
}
