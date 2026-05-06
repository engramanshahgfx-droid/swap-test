<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestLivewireLogin extends Command
{
    protected $signature = 'test:livewire-login';
    protected $description = 'Test login via Livewire form submission';

    public function handle()
    {
        $this->info("Testing Livewire login submission...");

        // First, get the login page to extract the CSRF token
        $client = new \GuzzleHttp\Client(['verify' => false, 'timeout' => 30, 'connect_timeout' => 5]);

        try {
            // Step 1: Get login page
            $this->info("\nStep 1: Fetching login page...");
            $response = $client->get('http://localhost:8000/admin/login');
            $html = (string) $response->getBody();

            // Extract CSRF token from meta tag
            if (preg_match('/<meta name="csrf-token" content="([^"]+)"/', $html, $matches)) {
                $csrf_token = $matches[1];
                $this->info("✓ CSRF token obtained");
            } else {
                $this->error("Could not find CSRF token");
                return;
            }

            // Step 2: Try to submit the login form via Livewire
            // Livewire uses a specific endpoint for form submissions
            $this->info("\nStep 2: Attempting login via /livewire/message/admin.pages.auth.login endpoint...");

            // The form component name and action
            $form_state = [
                'email' => 'admin@crewswap.com',
                'password' => 'password',
                'remember' => false,
            ];

            // Livewire payload
            $payload = [
                'actionName' => ['authenticate'],
                'actionArguments' => [[]],
                'updates' => [
                    [
                        'name' => 'data.email',
                        'value' => 'admin@crewswap.com',
                    ],
                    [
                        'name' => 'data.password',
                        'value' => 'password',
                    ],
                    [
                        'name' => 'data.remember',
                        'value' => false,
                    ],
                ],
            ];

            $response = $client->post('http://localhost:8000/admin/login', [
                'json' => $payload,
                'headers' => [
                    'X-CSRF-TOKEN' => $csrf_token,
                    'X-Livewire' => 'true',
                    'Content-Type' => 'application/json',
                ],
                'allow_redirects' => false,
            ]);

            $status = $response->getStatusCode();
            $this->info("Response status: {$status}");

            $body = (string) $response->getBody();
            $this->info("Response length: " . strlen($body) . " bytes");

            if ($status === 302) {
                $this->info("✓ Got redirect response");
                $location = $response->getHeader('location');
                if (is_array($location)) $location = $location[0] ?? '';
                $this->info("Location: {$location}");
            } else {
                // Try to parse as JSON
                $json = json_decode($body, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $this->line("Response JSON:");
                    $this->line(json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                } else {
                    $this->line("Response HTML (first 500 chars):");
                    $this->line(substr($body, 0, 500));
                }
            }

        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            if (method_exists($e, 'getResponse')) {
                $resp = $e->getResponse();
                if ($resp) {
                    $this->error("Response: " . $resp->getBody());
                }
            }
        }

        // Now check the logs
        $this->info("\n\nChecking application logs...");
        $log_path = storage_path('logs/laravel.log');
        if (file_exists($log_path)) {
            $this->info("Log file exists");
            $lines = explode("\n", file_get_contents($log_path));
            $recent = array_slice($lines, -30);
            $this->line("\n--- Last 30 log lines ---");
            foreach ($recent as $line) {
                if (strpos($line, 'FILAMENT') !== false || strpos($line, 'Login') !== false || strlen($line) > 10) {
                    $this->line($line);
                }
            }
        } else {
            $this->warn("Log file does not exist yet");
        }
    }
}

