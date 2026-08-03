<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserRoster;
use App\Models\UserTrip;
use App\Services\RosterParserService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class RosterController extends Controller
{
    protected RosterParserService $rosterParserService;

    public function __construct(RosterParserService $rosterParserService)
    {
        $this->rosterParserService = $rosterParserService;
    }

    /**
     * Upload and parse user roster.
     * Accepts file upload, raw text, or JSON roster payload.
     */
    public function uploadRoster(Request $request)
    {
        $request->validate([
            'file' => 'nullable|file|mimes:pdf,txt,json,png,jpg,jpeg|max:10240',
            'roster_text' => 'nullable|string',
            'json_data' => 'nullable|array',
            'month' => 'nullable|string',
            'year' => 'nullable|integer',
        ]);

        try {
            $user = $request->user();
            $filePath = null;
            $fileName = null;

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $fileName = $file->getClientOriginalName();
                $filePath = $file->store('rosters', 'public');
                $fullPath = Storage::disk('public')->path($filePath);
            } else {
                $fullPath = null;
            }

            $rawText = $request->input('roster_text');
            $jsonData = $request->input('json_data');

            // Fallback sample line roster text if empty payload passed for quick testing
            if (empty($fullPath) && empty($rawText) && empty($jsonData)) {
                $rawText = <<<EOT
Line No. 1107 (JED Economy Cabin Attendant 9 Z) Aug. 2026 PAGE 1 of 1
LINE1107 CR. 80.14 1: 2 3 4 5 6: 7 8: 9 10 11 12 13: 14 15: 16 17 18 19 20: 21 22: 23 24 25 26 27: 28 29: 30 31
#032 REPORT AT 04.20Z
FR 0127 780 05.50 JED 11.55 CDG 06.05 LAYOVER CDG 26.00
SA 0126 780 13.55 CDG 19.40 JED 05.45
#057 REPORT AT 05.40Z
MO 0341 77H 07.10 JED 12.30 ALG 05.20 LAYOVER ALG 50.05
WE 0340 77D 14.35 ALG 19.35 JED 05.00
#061 REPORT AT 07.40Z
FR 0588 33R 09.10 JED 12.00 DXB 02.50
FR 0589 33R 13.30 DXB 16.25 JED 02.55
#127 REPORT AT 15.25Z
TU 0389 33R 16.55 JED 19.05 CAI 02.10
TU 0388 33R 20.25 CAI 22.35 JED 02.10
#265 REPORT AT 20.55Z
TH 0844 789 22.25 JED 06.40 BKK 08.15 LAYOVER BKK 25.45
SA 0847 789 08.25 BKK 15.50 RUH 07.25
SA DH1049 77Z 18.00 RUH 19.45 JED 01.45
EOT;
            }

            $result = $this->rosterParserService->parseAndSaveRoster(
                userId: $user->id,
                filePath: $fullPath,
                rawText: $rawText,
                jsonInput: $jsonData,
                fileName: $fileName
            );

            return response()->json([
                'success' => true,
                'message' => 'Roster uploaded and parsed successfully',
                'data' => [
                    'roster' => $result['roster'],
                    'trips_count' => $result['trips_count'],
                    'trips' => $result['trips'],
                ],
            ], 201);
        } catch (Throwable $e) {
            Log::error('Roster upload error', [
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process roster upload: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Retrieve monthly roster calendar duties for the authenticated user.
     */
    public function getRoster(Request $request)
    {
        $user = $request->user();
        $month = $request->query('month');
        $year = $request->query('year', date('Y'));

        $query = UserRoster::where('user_id', $user->id);

        if ($month) {
            $query->where('month', 'like', "%{$month}%");
        }
        if ($year) {
            $query->where('year', $year);
        }

        $roster = $query->latest()->first();

        $tripsQuery = UserTrip::where('user_id', $user->id)
            ->with(['flight.airline', 'flight.planeType'])
            ->latest();

        if ($roster) {
            $tripsQuery->where('user_roster_id', $roster->id);
        }

        $trips = $tripsQuery->get();

        // Build monthly calendar structure
        $calendar = [];
        $targetYear = (int)($roster?->year ?? $year ?? date('Y'));
        $targetMonthStr = $roster?->month ?? $month ?? date('M');

        try {
            $baseDate = Carbon::parse("1 {$targetMonthStr} {$targetYear}");
        } catch (Throwable) {
            $baseDate = Carbon::now();
        }

        $daysInMonth = $baseDate->daysInMonth;

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dateStr = sprintf('%04d-%02d-%02d', $baseDate->year, $baseDate->month, $day);
            $dayOfWeek = Carbon::parse($dateStr)->format('l');

            $dayEvents = [];
            foreach ($trips as $trip) {
                $flight = $trip->flight;
                if ($flight && $flight->departure_date && $flight->departure_date->toDateString() === $dateStr) {
                    $dayEvents[] = [
                        'trip_id' => $trip->id,
                        'flight_number' => $flight->flight_number,
                        'departure' => $flight->departure_airport,
                        'arrival' => $flight->arrival_airport,
                        'report_time' => $trip->report_time ?? $flight->departure_time?->format('H:i'),
                        'release_time' => $trip->release_time ?? $flight->arrival_time?->format('H:i'),
                        'departure_time' => $flight->departure_time?->format('H:i'),
                        'arrival_time' => $flight->arrival_time?->format('H:i'),
                        'pairing_number' => $trip->pairing_number,
                        'duty_type' => $trip->duty_type,
                        'layover_location' => $trip->layover_location,
                        'layover_duration' => $trip->layover_duration,
                        'legs' => $trip->legs,
                    ];
                }
            }

            $calendar[] = [
                'day' => $day,
                'date' => $dateStr,
                'day_of_week' => $dayOfWeek,
                'events' => $dayEvents,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'roster' => $roster,
                'calendar' => $calendar,
                'trips' => $trips,
            ],
        ]);
    }

    /**
     * Delete a roster and its trips.
     */
    public function deleteRoster(Request $request, $id)
    {
        $user = $request->user();
        $roster = UserRoster::where('user_id', $user->id)->where('id', $id)->first();

        if (!$roster) {
            return response()->json([
                'success' => false,
                'message' => 'Roster not found',
            ], 404);
        }

        // Delete associated trips
        UserTrip::where('user_roster_id', $roster->id)->delete();
        $roster->delete();

        return response()->json([
            'success' => true,
            'message' => 'Roster and associated trips deleted successfully',
        ]);
    }
}
