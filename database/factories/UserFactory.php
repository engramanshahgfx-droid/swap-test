<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $airline = \App\Models\Airline::firstOrCreate(['code' => 'SV'], ['name' => 'Saudia']);
        $planeType = \App\Models\PlaneType::firstOrCreate(['code' => 'B777'], ['name' => 'Boeing 777', 'airline_id' => $airline->id]);
        $position = \App\Models\Position::firstOrCreate(['slug' => 'cabin-attendant'], ['name' => 'Cabin Attendant']);

        return [
            'full_name' => fake()->name(),
            'employee_id' => 'AD' . fake()->unique()->numberBetween(100000, 999999),
            'phone' => fake()->unique()->phoneNumber(),
            'country_base' => 'JED',
            'airline_id' => $airline->id,
            'plane_type_id' => $planeType->id,
            'position_id' => $position->id,
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
