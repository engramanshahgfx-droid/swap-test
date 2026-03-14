<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class TestRegisterFlow extends Command
{
    protected $signature = 'test:register-flow';
    protected $description = 'Test user registration and login flow';

    public function handle()
    {
        $this->info('=== Testing Register & Login Flow ===');

        // Create a test user
        $email = 'testuser' . time() . '@example.com';
        $password = 'password123';

        try {
            $user = User::create([
                'employee_id' => 'TEST' . time(),
                'full_name' => 'Test User',
                'phone' => '05512345' . rand(1000, 9999),
                'email' => $email,
                'country_base' => 'UAE',
                'airline_id' => 1,
                'plane_type_id' => 1,
                'position_id' => 1,
                'password' => Hash::make($password),
            ]);

            $this->info("✓ User created");
            $this->line("  Email: {$email}");
            $this->line("  Password: {$password}");
            $this->line("  ID: {$user->id}");

            // Assign a role
            $user->assignRole('flight_attendant');
            $this->info("✓ Role assigned: flight_attendant");

            // Verify login works
            $credentials = [
                'email' => $email,
                'password' => $password,
            ];

            $success = auth()->guard('web')->attempt($credentials);
            $this->info("✓ Auth attempt result: " . ($success ? "SUCCESS" : "FAILED"));

            if ($success) {
                $authenticated = auth()->guard('web')->user();
                $this->line("  Authenticated as: {$authenticated->email}");
            }

            // Test token creation
            $token = $user->createToken('test-api')->plainTextToken;
            $this->info("✓ API Token created: " . substr($token, 0, 20) . "...");

            $this->info("\n✅ Flow completed successfully!");
            $this->info("\nYou can now use these credentials to test the API:");
            $this->line("  Email: {$email}");
            $this->line("  Password: {$password}");

        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
        }
    }
}
