<?php

namespace Tests\Feature;

use App\Models\Airline;
use App\Models\PlaneType;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DashboardExpiringAccountsTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_users_with_expiring_activation(): void
    {
        $airline = Airline::create(['name' => 'Test Airline', 'code' => 'TA', 'country' => 'US', 'is_active' => true]);
        $planeType = PlaneType::create(['name' => 'A320', 'code' => 'A320', 'airline_id' => $airline->id, 'capacity' => 180, 'is_active' => true]);
        $position = Position::create(['name' => 'Flight Attendant', 'slug' => 'flight-attendant', 'description' => 'Crew', 'level' => 1]);

        $user = User::create([
            'employee_id' => 'EMP-100',
            'full_name' => 'Expiring User',
            'email' => 'expiring@example.com',
            'phone' => '1231231234',
            'country_base' => 'LHR',
            'airline_id' => $airline->id,
            'plane_type_id' => $planeType->id,
            'position_id' => $position->id,
            'password' => Hash::make('password'),
            'status' => 'active',
            'activation_start_date' => now()->toDateString(),
            'activation_end_date' => now()->addDays(5)->toDateString(),
            'grace_period_days' => 7,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('expiringAccounts', function ($expiringAccounts) use ($user) {
            return $expiringAccounts->contains(fn ($item) => $item->id === $user->id);
        });
    }

    public function test_activation_page_shows_selected_users_duration(): void
    {
        $airline = Airline::create(['name' => 'Test Airline', 'code' => 'TA', 'country' => 'US', 'is_active' => true]);
        $planeType = PlaneType::create(['name' => 'A320', 'code' => 'A320', 'airline_id' => $airline->id, 'capacity' => 180, 'is_active' => true]);
        $position = Position::create(['name' => 'Flight Attendant', 'slug' => 'flight-attendant', 'description' => 'Crew', 'level' => 1]);

        $admin = User::create([
            'employee_id' => 'ADMIN-100',
            'full_name' => 'Admin User',
            'email' => 'admin@example.com',
            'phone' => '1231231235',
            'country_base' => 'LHR',
            'airline_id' => $airline->id,
            'plane_type_id' => $planeType->id,
            'position_id' => $position->id,
            'password' => Hash::make('password'),
            'status' => 'active',
        ]);

        $user = User::create([
            'employee_id' => 'EMP-101',
            'full_name' => 'Temporary User',
            'email' => 'temporary@example.com',
            'phone' => '1231231236',
            'country_base' => 'LHR',
            'airline_id' => $airline->id,
            'plane_type_id' => $planeType->id,
            'position_id' => $position->id,
            'password' => Hash::make('password'),
            'status' => 'active',
        ]);

        $user->activateForDuration(6, 0, null, false, 7);

        $response = $this->actingAs($admin)->get(route('activation', ['user_id' => $user->id]));

        $response->assertStatus(200);
        $response->assertSee('6 months');
    }
}
