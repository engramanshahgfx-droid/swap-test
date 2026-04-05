<?php
require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$ids = \App\Models\User::where('email', 'like', 'otp_test_%@example.com')->pluck('id');

\Illuminate\Support\Facades\DB::table('user_trips')->whereIn('user_id', $ids)->delete();
$deletedUsers = \App\Models\User::whereIn('id', $ids)->delete();

echo 'Deleted users: ' . $deletedUsers . PHP_EOL;
