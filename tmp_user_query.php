<?php

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$search = $argv[1] ?? '%admin%';
foreach (App\Models\User::where('email', 'like', $search)->get() as $u) {
    echo $u->email . ' | ' . $u->status . ' | ' . $u->employee_id . PHP_EOL;
}
