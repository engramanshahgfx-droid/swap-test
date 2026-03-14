<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = \Illuminate\Http\Request::capture()
);

// Get test user
$user = \App\Models\User::find(12);
if ($user) {
    echo "User Found: {$user->email}\n";
    echo "---\n";
    
    // Get trips
    $trips = \App\Models\UserTrip::where('user_id', 12)->with('flight')->get();
    echo "User Trips Count: " . $trips->count() . "\n";
    
    foreach ($trips as $trip) {
        echo "Trip ID: {$trip->id}\n";
        echo "  Flight ID: {$trip->flight_id}\n";
        echo "  Status: {$trip->status}\n";
        echo "  Role: {$trip->role}\n";
        echo "  Notes: {$trip->notes}\n";
        if ($trip->flight) {
            echo "  Flight Number: {$trip->flight->flight_number}\n";
            echo "  Route: {$trip->flight->departure_airport} → {$trip->flight->arrival_airport}\n";
            echo "  Date: {$trip->flight->date}\n";
        }
        echo "---\n";
    }
} else {
    echo "User not found\n";
}
