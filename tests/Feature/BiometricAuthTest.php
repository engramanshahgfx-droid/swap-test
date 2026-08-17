<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BiometricAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_setup_code_updates_biometric_login_enabled_and_status(): void
    {
        $user = User::factory()->create([
            'biometric_login_enabled' => false,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/auth/setup-code', [
            'code' => '1234',
            'code_confirmation' => '1234',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'biometric_login_enabled' => true,
                ],
            ]);

        $user->refresh();
        $this->assertTrue($user->biometric_login_enabled);
        $this->assertNotNull($user->four_digit_code);

        $statusResponse = $this->getJson('/api/auth/biometric-status');
        $statusResponse->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'biometric_login_enabled' => true,
                    'has_4_digit_code' => true,
                ],
            ]);
    }

    public function test_setup_faceid_updates_biometric_login_enabled_and_status(): void
    {
        $user = User::factory()->create([
            'biometric_login_enabled' => false,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/auth/setup-faceid', [
            'public_key' => 'sample-public-key',
            'device_id' => 'sample-device-123',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'face_id_enabled' => true,
                    'device_id' => 'sample-device-123',
                ],
            ]);

        $user->refresh();
        $this->assertTrue($user->biometric_login_enabled);
        $this->assertTrue($user->face_id_enabled);
        $this->assertEquals('sample-device-123', $user->device_id);

        $statusResponse = $this->getJson('/api/auth/biometric-status');
        $statusResponse->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'biometric_login_enabled' => true,
                    'face_id_enabled' => true,
                    'device_id' => 'sample-device-123',
                ],
            ]);
    }

    public function test_disable_biometric_resets_biometric_login_enabled(): void
    {
        $user = User::factory()->create([
            'biometric_login_enabled' => true,
            'face_id_enabled' => true,
            'device_id' => 'device-1',
        ]);

        Sanctum::actingAs($user);

        $disableResponse = $this->deleteJson('/api/auth/disable-biometric');
        $disableResponse->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $user->refresh();
        $this->assertFalse($user->biometric_login_enabled);
        $this->assertFalse($user->face_id_enabled);

        $statusResponse = $this->getJson('/api/auth/biometric-status');
        $statusResponse->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'biometric_login_enabled' => false,
                    'face_id_enabled' => false,
                ],
            ]);
    }
}
