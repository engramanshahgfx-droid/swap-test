<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestApiEndpoints extends Command
{
    protected $signature = 'test:api-endpoints';
    protected $description = 'Test API endpoints';

    public function handle()
    {
        $base_url = 'http://127.0.0.1:8000';
        $this->info('Testing API Endpoints...');

        // Test Register
        $this->line("\n=== Testing POST /api/register ===");
        try {
            $response = Http::post("$base_url/api/register", [
                'employee_id' => 'TST001',
                'full_name' => 'Test User',
                'phone' => '0551234567',
                'email' => 'test@example.com',
                'country_base' => 'UAE',
                'airline_id' => 1,
                'plane_type_id' => 1,
                'position_id' => 1,
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

            if ($response->successful()) {
                $this->info('✓ Register endpoint works!');
                $data = $response->json();
                if ($data) {
                    $this->line(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                } else {
                    $this->line($response->body());
                }
            } else {
                $this->error('✗ Register endpoint failed');
                $this->error("Status: {$response->status()}");
                $data = $response->json();
                if ($data) {
                    $this->line(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                } else {
                    $this->line($response->body());
                }
            }
        } catch (\Exception $e) {
            $this->error('✗ Error testing register: ' . $e->getMessage());
        }

        // Test Login
        $this->line("\n=== Testing POST /api/login ===");
        try {
            $response = Http::post("$base_url/api/login", [
                'email' => 'admin@crewswap.com',
                'password' => 'password',
            ]);

            if ($response->successful()) {
                $this->info('✓ Login endpoint works!');
                $data = $response->json();
                if ($data) {
                    $this->line(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                } else {
                    $this->line($response->body());
                }
            } else {
                $this->error('✗ Login endpoint failed');
                $this->error("Status: {$response->status()}");
                $data = $response->json();
                if ($data) {
                    $this->line(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                } else {
                    $this->line($response->body());
                }
            }
        } catch (\Exception $e) {
            $this->error('✗ Error testing login: ' . $e->getMessage());
        }

        // Test Protected Route
        $this->line("\n=== Testing Protected Route (GET /api/my-trips) ===");
        try {
            // First get a token
            $loginResponse = Http::post("$base_url/api/login", [
                'email' => 'admin@crewswap.com',
                'password' => 'password',
            ]);

            if ($loginResponse->successful()) {
                $token = $loginResponse->json()['data']['token'] ?? null;
                if ($token) {
                    $response = Http::withToken($token)->get("$base_url/api/my-trips");
                    if ($response->successful()) {
                        $this->info('✓ Protected route works!');
                        $this->line(json_encode($response->json(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                    } else {
                        $this->error('✗ Protected route failed');
                        $this->error("Status: {$response->status()}");
                        $this->error($response->body());
                    }
                }
            }
        } catch (\Exception $e) {
            $this->error('✗ Error testing protected route: ' . $e->getMessage());
        }
    }
}
