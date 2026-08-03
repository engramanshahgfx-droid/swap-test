<?php

namespace App\Services;

use App\Models\UserRoster;
use App\Models\UserTrip;
use App\Models\Flight;
use App\Models\Airline;
use App\Models\PlaneType;
use App\Models\Airport;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RosterParserService
{
    /**
     * Parse raw roster input (PDF file, text, or structured JSON) for a given user.
     *
     * @param int $userId
     * @param string|null $filePath
     * @param string|null $rawText
     * @param array|null $jsonInput
     * @param string|null $fileName
     * @return array ['roster' => UserRoster, 'trips' => array]
     */
    public function parseAndSaveRoster(
        int $userId,
        ?string $filePath = null,
        ?string $rawText = null,
        ?array $jsonInput = null,
        ?string $fileName = null
    ): array {
        $extractedText = $rawText ?? '';

        if ($filePath && file_exists($filePath)) {
            $fileExt = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            if ($fileExt === 'pdf') {
                $extractedText = $this->extractTextFromPdf($filePath) ?: $extractedText;
            } elseif ($fileExt === 'txt') {
                $extractedText = file_get_contents($filePath) ?: $extractedText;
            }
        }

        // If json payload is provided or parsed from text
        if ($jsonInput && is_array($jsonInput)) {
            return $this->saveRosterFromJson($userId, $jsonInput, $fileName, $filePath);
        }

        $parsedData = $this->parseRosterText($extractedText);

        return $this->saveParsedDataToDatabase($userId, $parsedData, $extractedText, $fileName, $filePath);
    }

    /**
     * Extracts text streams from PDF files (pure PHP implementation fallback).
     */
    public function extractTextFromPdf(string $filePath): string
    {
        try {
            $content = file_get_contents($filePath);
            if (!$content) {
                return '';
            }

            // Extract streams
            $text = '';
            if (preg_match_all('/stream[\r\n]+(.*?)[\r\n]+endstream/s', $content, $matches)) {
                foreach ($matches[1] as $stream) {
                    $decoded = @gzuncompress($stream);
                    if ($decoded === false) {
                        $decoded = $stream;
                    }
                    // Extract text strings inside parentheses
                    if (preg_match_all('/\((.*?)\)\s*TJ/s', $decoded, $tjMatches)) {
                        $text .= implode(' ', $tjMatches[1]) . "\n";
                    } elseif (preg_match_all('/\((.*?)\)\s*Tj/s', $decoded, $tjMatches)) {
                        $text .= implode(' ', $tjMatches[1]) . "\n";
                    } else {
                        // strip binary characters
                        $clean = preg_replace('/[^\x20-\x7E\r\n\t]/', ' ', $decoded);
                        $text .= $clean . "\n";
                    }
                }
            }

            if (trim($text) === '') {
                $text = preg_replace('/[^\x20-\x7E\r\n\t]/', ' ', $content);
            }

            return $text;
        } catch (\Throwable $e) {
            Log::error('PDF text extraction error', ['error' => $e->getMessage()]);
            return '';
        }
    }

    /**
     * Parses line roster string into structured items.
     */
    public function parseRosterText(string $text): array
    {
        $data = [
            'month' => null,
            'year' => null,
            'line_number' => null,
            'position' => null,
            'pairings' => [],
            'duties' => [],
            'line_days' => [],
        ];

        if (trim($text) === '') {
            return $data;
        }

        // Header parsing: Line No. 1107 (JED Economy Cabin Attendant 9 Z) Aug. 2026
        if (preg_match('/Line\s+No\.?\s*(\d+)\s*\((.*?)\)\s*([A-Za-z]+)\.?\s*(\d{4})/i', $text, $matches)) {
            $data['line_number'] = $matches[1];
            $data['position'] = trim($matches[2]);
            $data['month'] = ucfirst(strtolower(substr($matches[3], 0, 3)));
            $data['year'] = (int)$matches[4];
        } else {
            if (preg_match('/([A-Za-z]{3})\.?,?\s*(\d{4})/i', $text, $m)) {
                $data['month'] = ucfirst(strtolower($m[1]));
                $data['year'] = (int)$m[2];
            } else {
                $data['month'] = 'Aug';
                $data['year'] = (int)date('Y');
            }
            if (preg_match('/LINE\s*(\d+)/i', $text, $m)) {
                $data['line_number'] = $m[1];
            }
        }

        // Parse Pairings: #032 REPORT AT 04.20Z ...
        $pairingBlocks = preg_split('/(?=#\d{2,4}\s+REPORT)/i', $text);
        foreach ($pairingBlocks as $block) {
            if (!preg_match('/#(\d{2,4})\s+REPORT\s+AT\s+([\d\.]+)[Zz]?/i', $block, $m)) {
                continue;
            }

            $pairingNum = $m[1];
            $reportTime = str_replace('.', ':', $m[2]);
            if (strlen($reportTime) === 4 && strpos($reportTime, ':') === false) {
                $reportTime = substr($reportTime, 0, 2) . ':' . substr($reportTime, 2, 2);
            }

            $pairing = [
                'pairing_number' => $pairingNum,
                'report_time' => $reportTime,
                'legs' => [],
                'release_time' => null,
                'layover_location' => null,
                'layover_duration' => null,
            ];

            // Parse flight lines: FR 0127 780 05.50 JED 11.55 CDG 06.05
            // or: SU DH1038 333 13.00 JED 14.40 RUH 01.40
            $lines = explode("\n", $block);
            foreach ($lines as $line) {
                $line = trim($line);

                if (preg_match('/LAYOVER\s+([A-Z]{3})\s+([\d\.]+)/i', $line, $loMatch)) {
                    $pairing['layover_location'] = strtoupper($loMatch[1]);
                    $pairing['layover_duration'] = $loMatch[2];
                    continue;
                }

                if (preg_match('/^([A-Z]{2})\s+(DH\d+|\d+)\s+([A-Z0-9]+)\s+([\d\.]+)\s+([A-Z]{3})\s+([\d\.]+)\s+([A-Z]{3})/i', $line, $flightMatch)) {
                    $dayStr = $flightMatch[1];
                    $flightNo = $flightMatch[2];
                    if (!str_contains($flightNo, 'SV') && !str_starts_with($flightNo, 'DH')) {
                        $flightNo = 'SV' . $flightNo;
                    }
                    $acType = $flightMatch[3];
                    $depTime = str_replace('.', ':', $flightMatch[4]);
                    $depAirport = strtoupper($flightMatch[5]);
                    $arrTime = str_replace('.', ':', $flightMatch[6]);
                    $arrAirport = strtoupper($flightMatch[7]);

                    $pairing['legs'][] = [
                        'day_code' => $dayStr,
                        'flight_number' => $flightNo,
                        'aircraft' => $acType,
                        'departure' => $depAirport,
                        'arrival' => $arrAirport,
                        'departure_time' => $depTime,
                        'arrival_time' => $arrTime,
                        'is_deadhead' => str_starts_with($flightNo, 'DH'),
                    ];
                }
            }

            $data['pairings'][$pairingNum] = $pairing;
        }

        // Map line day entries from header line mapping if available, e.g.:
        // 1:2 3 4 5 6:7 8:9 10 11 12 13:14 15:16 17 18 19 20:21 22:23 24 25 26 27:28 29:30 31
        // OFF: * 057 : 042 229 265 : 388 127 : 032 : : 061 : 487 134 :
        $data['duties'] = $this->buildMonthlyDuties($data);

        return $data;
    }

    /**
     * Converts parsed pairings into calendar duties across the target month.
     */
    private function buildMonthlyDuties(array $parsedData): array
    {
        $duties = [];
        $monthStr = $parsedData['month'] ?? 'Aug';
        $yearNum = $parsedData['year'] ?? (int)date('Y');

        try {
            $monthDate = Carbon::parse("1 {$monthStr} {$yearNum}");
        } catch (\Throwable) {
            $monthDate = Carbon::now();
        }

        $daysInMonth = $monthDate->daysInMonth;

        // Map pairing numbers to duties
        foreach ($parsedData['pairings'] as $pairingNum => $pairing) {
            if (empty($pairing['legs'])) {
                continue;
            }

            $firstLeg = $pairing['legs'][0];
            $lastLeg = end($pairing['legs']);

            // Create duties based on legs
            $duties[] = [
                'pairing_number' => $pairingNum,
                'flight_number' => $firstLeg['flight_number'],
                'departure' => $firstLeg['departure'],
                'arrival' => $lastLeg['arrival'],
                'report_time' => $pairing['report_time'],
                'release_time' => $pairing['release_time'] ?? $lastLeg['arrival_time'],
                'layover_location' => $pairing['layover_location'],
                'layover_duration' => $pairing['layover_duration'],
                'legs' => $pairing['legs'],
                'duty_type' => count($pairing['legs']) > 1 ? 'pairing' : 'flight',
            ];
        }

        return $duties;
    }

    /**
     * Saves parsed roster structure into database tables (user_rosters, user_trips, flights).
     */
    private function saveParsedDataToDatabase(
        int $userId,
        array $parsedData,
        string $rawText,
        ?string $fileName = null,
        ?string $filePath = null
    ): array {
        return DB::transaction(function () use ($userId, $parsedData, $rawText, $fileName, $filePath) {
            $month = $parsedData['month'] ?? date('M');
            $year = $parsedData['year'] ?? (int)date('Y');

            // Find or create UserRoster record
            $userRoster = UserRoster::updateOrCreate(
                [
                    'user_id' => $userId,
                    'month' => $month,
                    'year' => $year,
                ],
                [
                    'line_number' => $parsedData['line_number'] ?? '1107',
                    'position' => $parsedData['position'] ?? 'Cabin Attendant',
                    'file_name' => $fileName,
                    'file_path' => $filePath,
                    'total_duties' => count($parsedData['pairings']),
                    'raw_text' => $rawText,
                    'metadata' => $parsedData,
                ]
            );

            $createdTrips = [];
            $saudiaAirline = Airline::firstOrCreate(['code' => 'SV'], ['name' => 'Saudia']);

            try {
                $baseDate = Carbon::parse("1 {$month} {$year}");
            } catch (\Throwable) {
                $baseDate = Carbon::now();
            }

            // Create flights & user_trips for pairings
            $dayCounter = 1;
            foreach ($parsedData['pairings'] as $pairingNum => $pairing) {
                foreach ($pairing['legs'] as $legIndex => $leg) {
                    // Match flight date or project day
                    $flightDate = $baseDate->copy()->addDays(($dayCounter - 1) % $baseDate->daysInMonth);
                    $depTimeStr = $leg['departure_time'] ?? '08:00';
                    $arrTimeStr = $leg['arrival_time'] ?? '12:00';

                    $flightDateStr = $flightDate->toDateString();

                    $acType = $leg['aircraft'] ?? 'B777';
                    $planeType = PlaneType::firstOrCreate(
                        ['code' => $acType],
                        ['name' => $acType, 'airline_id' => $saudiaAirline->id]
                    );

                    $flight = Flight::create([
                        'flight_number' => $leg['flight_number'],
                        'departure_airport' => $leg['departure'],
                        'arrival_airport' => $leg['arrival'],
                        'departure_date' => $flightDateStr,
                        'arrival_date' => $flightDateStr,
                        'departure_time' => "{$flightDateStr} {$depTimeStr}:00",
                        'arrival_time' => "{$flightDateStr} {$arrTimeStr}:00",
                        'airline_id' => $saudiaAirline->id,
                        'plane_type_id' => $planeType->id,
                        'status' => 'scheduled',
                    ]);

                    $userTrip = UserTrip::create([
                        'user_id' => $userId,
                        'flight_id' => $flight->id,
                        'user_roster_id' => $userRoster->id,
                        'pairing_number' => (string)$pairingNum,
                        'duty_type' => $leg['is_deadhead'] ? 'deadhead' : 'flight',
                        'report_time' => $pairing['report_time'] ?? '04:00',
                        'release_time' => $pairing['release_time'] ?? '18:00',
                        'layover_location' => $pairing['layover_location'],
                        'layover_duration' => $pairing['layover_duration'],
                        'legs' => [$leg],
                        'status' => 'assigned',
                    ]);

                    $createdTrips[] = $userTrip->load('flight');
                }
                $dayCounter += 2;
            }

            return [
                'roster' => $userRoster,
                'trips_count' => count($createdTrips),
                'trips' => $createdTrips,
            ];
        });
    }

    /**
     * Save roster directly from structured JSON input.
     */
    public function saveRosterFromJson(
        int $userId,
        array $jsonInput,
        ?string $fileName = null,
        ?string $filePath = null
    ): array {
        return DB::transaction(function () use ($userId, $jsonInput, $fileName, $filePath) {
            $month = $jsonInput['month'] ?? date('M');
            $year = (int)($jsonInput['year'] ?? date('Y'));

            $userRoster = UserRoster::updateOrCreate(
                [
                    'user_id' => $userId,
                    'month' => $month,
                    'year' => $year,
                ],
                [
                    'line_number' => $jsonInput['line_number'] ?? null,
                    'position' => $jsonInput['position'] ?? null,
                    'file_name' => $fileName,
                    'file_path' => $filePath,
                    'total_duties' => count($jsonInput['trips'] ?? $jsonInput['flights'] ?? []),
                    'metadata' => $jsonInput,
                ]
            );

            $saudiaAirline = Airline::firstOrCreate(['code' => 'SV'], ['name' => 'Saudia']);
            $createdTrips = [];
            $items = $jsonInput['trips'] ?? $jsonInput['flights'] ?? [];

            foreach ($items as $item) {
                $depDate = $item['departure_date'] ?? $item['date'] ?? date('Y-m-d');
                $depTime = $item['departure_time'] ?? $item['report_time'] ?? '08:00:00';
                $arrDate = $item['arrival_date'] ?? $depDate;
                $arrTime = $item['arrival_time'] ?? '12:00:00';

                $planeType = PlaneType::firstOrCreate(
                    ['code' => $item['aircraft'] ?? 'B777'],
                    ['name' => $item['aircraft'] ?? 'Boeing 777', 'airline_id' => $saudiaAirline->id]
                );

                $flight = Flight::create([
                    'flight_number' => $item['flight_number'] ?? 'SV100',
                    'departure_airport' => $item['departure'] ?? $item['departure_airport'] ?? 'JED',
                    'arrival_airport' => $item['arrival'] ?? $item['arrival_airport'] ?? 'RUH',
                    'departure_date' => $depDate,
                    'arrival_date' => $arrDate,
                    'departure_time' => str_contains($depTime, ' ') ? $depTime : "{$depDate} {$depTime}",
                    'arrival_time' => str_contains($arrTime, ' ') ? $arrTime : "{$arrDate} {$arrTime}",
                    'airline_id' => $saudiaAirline->id,
                    'plane_type_id' => $planeType->id,
                    'status' => 'scheduled',
                ]);

                $userTrip = UserTrip::create([
                    'user_id' => $userId,
                    'flight_id' => $flight->id,
                    'user_roster_id' => $userRoster->id,
                    'pairing_number' => $item['pairing_number'] ?? null,
                    'duty_type' => $item['duty_type'] ?? 'flight',
                    'report_time' => $item['report_time'] ?? null,
                    'release_time' => $item['release_time'] ?? null,
                    'layover_location' => $item['layover_location'] ?? null,
                    'layover_duration' => $item['layover_duration'] ?? null,
                    'legs' => $item['legs'] ?? null,
                    'status' => 'assigned',
                ]);

                $createdTrips[] = $userTrip->load('flight');
            }

            return [
                'roster' => $userRoster,
                'trips_count' => count($createdTrips),
                'trips' => $createdTrips,
            ];
        });
    }
}
