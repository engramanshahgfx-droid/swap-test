<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

// Create a test request
$request = \Illuminate\Http\Request::create('/api/my-trips', 'GET');
$request->headers->set('Authorization', 'Bearer test-token');
$request->headers->set('Accept', 'application/json');

// Pass through the middleware stack
$response = $kernel->handle($request);

echo "Status: " . $response->getStatusCode() . "\n";
echo "Content-Type: " . $response->headers->get('Content-Type') . "\n";
echo "Body:\n";
echo $response->getContent();
