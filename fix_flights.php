<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

// Update flights with dates
$flights = \App\Models\Flight::take(3)->get();
foreach ($flights as $i => $flight) {
    $flight->update([
        'date' => now()->addDays($i + 1),
        'time' => now()->addDays($i + 1)->addHours(8 + ($i * 2)),
            ]);
    echo "Updated flight {$flight->id}: {$flight->flight_number} on " . $flight->date->format('Y-m-d H:i') . "\n";
}
