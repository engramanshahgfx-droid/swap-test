<?php

namespace Tests\Feature;

use App\Models\Airline;
use App\Models\PlaneType;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ActivationExpiryTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_marks_users_as_expired_when_activation_period_ends(): void
    {
        $airline = Airline::create(['name' => 'Test Airline', 'code' => 'TA', 'country' => 'US', 'is_active' => true]);
        $planeType = PlaneType::create(['name' => 'A320', 'code' => 'A320', 'airline_id' => $airline->id, 'capacity' => 180, 'is_active' => true]);
        $position = Position::create(['name' => 'Flight Attendant', 'slug' => 'flight-attendant', 'description' => 'Crew', 'level' => 1]);

        $user = User::create([
            'employee_id' => 'EMP-001',
            'full_name' => 'Demo User',
            'email' => 'demo@example.com',
            'phone' => '1234567890',
            'country_base' => 'LHR',
            'airline_id' => $airline->id,
            'plane_type_id' => $planeType->id,
            'position_id' => $position->id,
            'password' => Hash::make('password'),
            'status' => 'active',
            'activation_start_date' => now()->subDays(20),
            'activation_end_date' => now()->subDay(),
            'grace_period_days' => 7,
        ]);

        $user->processActivationExpiry();

        $freshUser = $user->fresh();

        $this->assertSame('expired', $freshUser->status);
        $this->assertNotNull($freshUser->grace_period_end_date);
    }
}
