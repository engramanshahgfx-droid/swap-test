<?php

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

foreach (App\Models\User::where('email', 'like', '%admin%')->get() as $u) {
    echo $u->email . ' | ' . $u->status . ' | ' . $u->employee_id . PHP_EOL;
}
