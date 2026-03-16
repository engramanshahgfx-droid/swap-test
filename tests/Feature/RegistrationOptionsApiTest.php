<?php

namespace Tests\Feature;

use App\Models\Airline;
use App\Models\PlaneType;
use App\Models\Position;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationOptionsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_registration_options_for_mobile_registration(): void
    {
        $activeAirline = Airline::create([
            'name' => 'Air China',
            'code' => 'CA',
            'country' => 'China',
            'is_active' => true,
        ]);

        $inactiveAirline = Airline::create([
            'name' => 'Inactive Air',
            'code' => 'IA',
            'country' => 'Test',
            'is_active' => false,
        ]);

        PlaneType::create([
            'name' => 'Boeing 787',
            'code' => 'B787',
            'airline_id' => $activeAirline->id,
            'capacity' => 250,
            'is_active' => true,
        ]);

        PlaneType::create([
            'name' => 'Retired Aircraft',
            'code' => 'RET1',
            'airline_id' => $activeAirline->id,
            'capacity' => 1,
            'is_active' => false,
        ]);

        PlaneType::create([
            'name' => 'Inactive Airline Plane',
            'code' => 'IAP1',
            'airline_id' => $inactiveAirline->id,
            'capacity' => 200,
            'is_active' => true,
        ]);

        Position::create([
            'name' => 'Flight Attendant',
            'slug' => 'flight-attendant',
            'level' => 1,
        ]);

        Position::create([
            'name' => 'Purser',
            'slug' => 'purser',
            'level' => 2,
        ]);

        $response = $this->getJson('/api/registration-options');

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data.airlines')
            ->assertJsonCount(1, 'data.plane_types')
            ->assertJsonCount(2, 'data.positions')
            ->assertJsonPath('data.airlines.0.name', 'Air China')
            ->assertJsonPath('data.plane_types.0.airline_name', 'Air China');
    }

    public function test_it_can_filter_plane_types_by_airline_id(): void
    {
        $firstAirline = Airline::create([
            'name' => 'Air One',
            'code' => 'AO',
            'is_active' => true,
        ]);

        $secondAirline = Airline::create([
            'name' => 'Air Two',
            'code' => 'AT',
            'is_active' => true,
        ]);

        PlaneType::create([
            'name' => 'A320',
            'code' => 'A320',
            'airline_id' => $firstAirline->id,
            'is_active' => true,
        ]);

        PlaneType::create([
            'name' => 'B737',
            'code' => 'B737',
            'airline_id' => $secondAirline->id,
            'is_active' => true,
        ]);

        Position::create([
            'name' => 'Captain',
            'slug' => 'captain',
            'level' => 1,
        ]);

        $response = $this->getJson("/api/registration-options?airline_id={$firstAirline->id}");

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data.plane_types')
            ->assertJsonPath('data.plane_types.0.name', 'A320')
            ->assertJsonPath('data.plane_types.0.airline_id', $firstAirline->id);
    }
}