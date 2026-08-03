<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserRoster;
use App\Models\UserTrip;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RosterUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_upload_and_parse_roster_text()
    {
        $user = User::factory()->create([
            'email' => 'roster_tester@crewswap.com',
        ]);

        Sanctum::actingAs($user);

        $rosterText = <<<EOT
Line No. 1107 (JED Economy Cabin Attendant 9 Z) Aug. 2026 PAGE 1 of 1
#032 REPORT AT 04.20Z
FR 0127 780 05.50 JED 11.55 CDG 06.05 LAYOVER CDG 26.00
SA 0126 780 13.55 CDG 19.40 JED 05.45
CREDIT: 11.50 BLOCK: 11.50 TAFB: 039.50
#057 REPORT AT 05.40Z
MO 0341 77H 07.10 JED 12.30 ALG 05.20 LAYOVER ALG 50.05
WE 0340 77D 14.35 ALG 19.35 JED 05.00
CREDIT: 10.20 BLOCK: 10.20 TAFB: 062.25
EOT;

        $response = $this->postJson('/api/roster/upload', [
            'roster_text' => $rosterText,
            'month' => 'Aug',
            'year' => 2026,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Roster uploaded and parsed successfully',
            ]);

        $this->assertDatabaseHas('user_rosters', [
            'user_id' => $user->id,
            'month' => 'Aug',
            'year' => 2026,
            'line_number' => '1107',
        ]);

        $this->assertDatabaseHas('user_trips', [
            'user_id' => $user->id,
            'pairing_number' => '032',
        ]);

        $this->assertDatabaseHas('user_trips', [
            'user_id' => $user->id,
            'pairing_number' => '057',
        ]);
    }

    public function test_user_can_retrieve_monthly_roster_calendar()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Upload first
        $this->postJson('/api/roster/upload', [
            'month' => 'Aug',
            'year' => 2026,
        ]);

        $response = $this->getJson('/api/roster?month=Aug&year=2026');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'data' => [
                    'roster',
                    'calendar',
                    'trips',
                ],
            ]);
    }

    public function test_user_can_delete_roster()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $uploadResponse = $this->postJson('/api/roster/upload', [
            'month' => 'Aug',
            'year' => 2026,
        ]);

        $rosterId = $uploadResponse->json('data.roster.id');

        $deleteResponse = $this->deleteJson("/api/roster/{$rosterId}");

        $deleteResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Roster and associated trips deleted successfully',
            ]);

        $this->assertDatabaseMissing('user_rosters', [
            'id' => $rosterId,
        ]);
    }
}
