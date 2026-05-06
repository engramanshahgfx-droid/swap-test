<?php

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$users = App\Models\User::where('email', 'like', '%crewswap.com')->get();
foreach ($users as $user) {
    $user->email = str_replace('crewswap.com', 'CrewSwap.com', $user->email);
    echo "Updating {$user->id} -> {$user->email}\n";
    $user->save();
}

