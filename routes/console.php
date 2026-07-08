<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Console\Commands\ProcessUserActivationExpiry;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('users:process-activation-expiry', function () {
    $this->call(ProcessUserActivationExpiry::class);
})->purpose('Process user activation expiry, grace period, and status transitions');
