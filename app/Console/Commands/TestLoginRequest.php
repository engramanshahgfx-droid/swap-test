<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestLoginRequest extends Command
{
    protected $signature = 'test:login-request';
    protected $description = 'Test login request';

    public function handle()
    {
        // Get CSRF token first
        $client = new \GuzzleHttp\Client();

        $this->info('Step 1: Get login form (to obtain CSRF token)...');
        try {
            $response = $client->get('http://localhost:8000/admin/login');
            $html = (string) $response->getBody();

            // Extract CSRF token
            if (preg_match('/<input[^>]*name="csrf_token"[^>]*value="([^"]+)"/', $html, $matches)) {
                $csrf_token = $matches[1];
                $this->info("✓ CSRF token obtained: " . substr($csrf_token, 0, 20) . "...");
            } else {
                $this->error("✗ Could not find CSRF token in form");
                return;
            }

            // Try login
            $this->info("\nStep 2: Submit login form...");
            $login_response = $client->post('http://localhost:8000/admin/login', [
                'form_params' => [
                    'email' => 'admin@flightSwap .com',
                    'password' => 'password',
                    'csrf_token' => $csrf_token,
                ],
                'allow_redirects' => false,
            ]);

            $status = $login_response->getStatusCode();
            $this->info("Response Status: {$status}");

            if ($status === 302) {
                $location = $login_response->getHeader('Location');
                $this->info("✓ Redirect to: " . implode(', ', $location));
            } else {
                $body = (string) $login_response->getBody();
                if (strpos($body, 'These credentials') !== false) {
                    $this->error("✗ Got credential error in response");
                } else {
                    $this->line("Response body (first 500 chars):");
                    $this->line(substr($body, 0, 500));
                }
            }

        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
        }
    }
}
