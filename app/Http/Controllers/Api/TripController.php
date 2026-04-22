<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PublishTripRequest;
use App\Models\PublishedTrip;
use App\Models\Flight;
use App\Models\UserTrip;
use App\Models\SwapRequest;
use App\Services\SwapService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

class TripController extends Controller
{
    protected $swapService;
    private static array $columnExistsCache = [];

    private function hasColumn(string $table, string $column): bool
    {
        $key = $table . '.' . $column;

        if (!array_key_exists($key, self::$columnExistsCache)) {
            self::$columnExistsCache[$key] = Schema::hasColumn($table, $column);
        }

        return self::$columnExistsCache[$key];
    }

    private function serializeAskLo(string|array|null $askLo): ?string
    {
        if ($askLo === null) {
            return null;
        }

        if (is_string($askLo)) {
            $normalized = trim($askLo);
            return $normalized === '' ? null : $normalized;
        }

        if ($askLo === []) {
            return null;
        }

        return json_encode($askLo, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function parseAskLo(?string $askLo): string|array|null
    {
        if ($askLo === null) {
            return null;
        }

        $normalized = trim($askLo);
        if ($normalized === '') {
            return null;
        }

        $decoded = json_decode($normalized, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        return $normalized;
    }

    private function serializeOfferLo(string|array|null $offerLo): ?string
    {
        return $this->serializeAskLo($offerLo);
    }

    private function parseOfferLo(?string $offerLo): string|array|null
    {
        return $this->parseAskLo($offerLo);
    }

    private function normalizeFlightTime(?string $value, string $default): string
    {
        if ($value === null) {
            return $default;
        }

        $normalized = trim($value);
        if ($normalized === '') {
            return $default;
        }

        // Date-only inputs belong to *_date fields, not time columns.
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $normalized) === 1) {
            return $default;
        }

        if (preg_match('/^([01]\\d|2[0-3]):[0-5]\\d(:[0-5]\\d)?$/', $normalized) === 1) {
            return strlen($normalized) === 5 ? $normalized . ':00' : $normalized;
        }

        try {
            return \Carbon\Carbon::parse($normalized)->format('H:i:s');
        } catch (\Throwable) {
            return $default;
        }
    }

    private function parseLegacyLoLine(?string $value): string|array|null
    {
        if ($value === null) {
            return null;
        }

        $normalized = trim($value);
        if ($normalized === '') {
            return null;
        }

        if (preg_match('/^([01]\d|2[0-3]):[0-5]\d\s+(.+)$/', $normalized, $matches) === 1) {
            return [[
                'time' => substr($normalized, 0, 5),
                'type' => trim($matches[2]),
            ]];
        }

        return $normalized;
    }

    private function parseLegacyTripFieldsFromNotes(?string $notes): array
    {
        if ($notes === null || trim($notes) === '') {
            return [];
        }

        // Some legacy clients sent literal "\\n" in notes; normalize first.
        $normalizedNotes = str_replace(["\\r\\n", "\\n", "\\r"], "\n", $notes);

        $parsed = [];

        if (preg_match('/\bReport[\s_]*time\s*:\s*([^\r\n]+)/i', $normalizedNotes, $matches) === 1) {
            $parsed['report_time'] = trim($matches[1]);
        }

        if (preg_match('/\bLegs\s*:\s*(\d+)/i', $normalizedNotes, $matches) === 1) {
            $parsed['legs'] = (int) $matches[1];
        }

        if (preg_match('/\bFly[\s_]*type\s*:\s*([^\r\n]+)/i', $normalizedNotes, $matches) === 1) {
            $parsed['fly_type'] = trim($matches[1]);
        }

        if (preg_match('/\bOffer[\s_]*L[O0]\s*:\s*([^\r\n]+)/i', $normalizedNotes, $matches) === 1) {
            $parsed['offer_lo'] = $this->parseLegacyLoLine($matches[1]);
        }

        if (preg_match('/\bAsk[\s_]*L[O0]\s*:\s*([^\r\n]+)/i', $normalizedNotes, $matches) === 1) {
            $parsed['ask_lo'] = $this->parseLegacyLoLine($matches[1]);
        }

        if (preg_match('/\bDetails?\s*:\s*([^\r\n]+)/i', $normalizedNotes, $matches) === 1) {
            $parsed['details'] = trim($matches[1]);
        }

        if (!array_key_exists('details', $parsed) && $parsed !== []) {
            $lines = preg_split('/\r\n|\r|\n/', $normalizedNotes) ?: [];

            foreach ($lines as $line) {
                $candidate = trim($line);

                if ($candidate === '') {
                    continue;
                }

                if (preg_match('/^(Report[\s_]*time|Legs|Fly[\s_]*type|Offer[\s_]*L[O0]|Ask[\s_]*L[O0]|Details?)\s*:/i', $candidate) === 1) {
                    continue;
                }

                $parsed['details'] = $candidate;
                break;
            }
        }

        return $parsed;
    }

    private function isBlankString(mixed $value): bool
    {
        return is_string($value) && trim($value) === '';
    }

    private function valueOrFallback(mixed $value, mixed $fallback): mixed
    {
        if ($value === null || $this->isBlankString($value)) {
            return $fallback;
        }

        return $value;
    }

    public function __construct(SwapService $swapService)
    {
        $this->swapService = $swapService;
    }

    public function myTrips(Request $request)
    {
        $perPage = max(1, min((int) $request->integer('per_page', 20), 100));

        $trips = UserTrip::where('user_id', $request->user()->id)
            ->with(['flight' => function ($query) {
                $query->with(['airline', 'planeType']);
            }, 'publishedTrips' => function ($query) {
                $query->latest('id');
            }])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->through(function ($trip) {
                $publishedTrip = $trip->publishedTrips->first();
                $legacyTripDetails = $this->parseLegacyTripFieldsFromNotes($publishedTrip?->notes);
                $departureDate = $trip->flight?->departure_date ? $trip->flight->departure_date->format('Y-m-d') : null;
                $arrivalDate = $trip->flight?->arrival_date ? $trip->flight->arrival_date->format('Y-m-d') : null;

                return [
                    'id' => $trip->id,
                    'flight' => [
                        'id' => $trip->flight?->id,
                        'number' => $trip->flight?->flight_number,
                        'departure' => $trip->flight?->departure_airport,
                        'arrival' => $trip->flight?->arrival_airport,
                        'departure_date' => $departureDate,
                        'arrival_date' => $arrivalDate,
                        'departure_time' => $trip->flight?->departure_time ? $trip->flight->departure_time->format('H:i:s') : null,
                        'arrival_time' => $trip->flight?->arrival_time ? $trip->flight->arrival_time->format('H:i:s') : null,
                        'duration' => $trip->flight?->formatted_duration,
                        'status' => $trip->flight?->status,
                    ],
                    'flight_number' => $this->valueOrFallback($publishedTrip?->flight_number, $trip->flight?->flight_number),
                    'departure_date' => $departureDate,
                    'arrival_date' => $arrivalDate,
                    'legs' => $this->valueOrFallback($publishedTrip?->legs, $legacyTripDetails['legs'] ?? null),
                    'fly_type' => $this->valueOrFallback($publishedTrip?->fly_type, $legacyTripDetails['fly_type'] ?? null),
                    'report_time' => $this->valueOrFallback($publishedTrip?->report_time, $legacyTripDetails['report_time'] ?? null),
                    'offer_lo' => $this->valueOrFallback($this->parseOfferLo($publishedTrip?->offer_lo), $legacyTripDetails['offer_lo'] ?? null),
                    'ask_lo' => $this->valueOrFallback($this->parseAskLo($publishedTrip?->ask_lo), $legacyTripDetails['ask_lo'] ?? null),
                    'details' => $this->valueOrFallback($publishedTrip?->details, $legacyTripDetails['details'] ?? null),
                    'status' => $trip->status,
                    'role' => $trip->role,
                    'notes' => $trip->notes,
                    'created_at' => $trip->created_at,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $trips->items(),
                'pagination' => [
                    'current_page' => $trips->currentPage(),
                    'last_page' => $trips->lastPage(),
                    'per_page' => $trips->perPage(),
                    'total' => $trips->total(),
                ],
            ],
        ]);
    }

    public function tripDetails($id)
    {
        $trip = UserTrip::with(['flight', 'user', 'flight.airline', 'flight.planeType', 'publishedTrips' => function ($query) {
            $query->latest('id');
        }])
            ->findOrFail($id);

        $publishedTrip = $trip->publishedTrips->first();
        $legacyTripDetails = $this->parseLegacyTripFieldsFromNotes($publishedTrip?->notes);
        $departureDate = $trip->flight?->departure_date ? $trip->flight->departure_date->format('Y-m-d') : null;
        $arrivalDate = $trip->flight?->arrival_date ? $trip->flight->arrival_date->format('Y-m-d') : null;

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $trip->id,
                'user' => [
                    'id' => $trip->user->id,
                    'name' => $trip->user->full_name,
                    'employee_id' => $trip->user->employee_id,
                ],
                'flight' => [
                    'id' => $trip->flight?->id,
                    'number' => $trip->flight?->flight_number,
                    'departure' => $trip->flight?->departure_airport,
                    'arrival' => $trip->flight?->arrival_airport,
                    'departure_date' => $departureDate,
                    'arrival_date' => $arrivalDate,
                    'departure_time' => $trip->flight?->departure_time ? $trip->flight->departure_time->format('H:i:s') : null,
                    'arrival_time' => $trip->flight?->arrival_time ? $trip->flight->arrival_time->format('H:i:s') : null,
                    'duration' => $trip->flight?->formatted_duration,
                    'status' => $trip->flight?->status,
                    'airline' => $trip->flight?->airline?->name,
                    'plane_type' => $trip->flight?->planeType?->name,
                ],
                'flight_number' => $this->valueOrFallback($publishedTrip?->flight_number, $trip->flight?->flight_number),
                'departure_date' => $departureDate,
                'arrival_date' => $arrivalDate,
                'legs' => $this->valueOrFallback($publishedTrip?->legs, $legacyTripDetails['legs'] ?? null),
                'fly_type' => $this->valueOrFallback($publishedTrip?->fly_type, $legacyTripDetails['fly_type'] ?? null),
                'report_time' => $this->valueOrFallback($publishedTrip?->report_time, $legacyTripDetails['report_time'] ?? null),
                'offer_lo' => $this->valueOrFallback($this->parseOfferLo($publishedTrip?->offer_lo), $legacyTripDetails['offer_lo'] ?? null),
                'ask_lo' => $this->valueOrFallback($this->parseAskLo($publishedTrip?->ask_lo), $legacyTripDetails['ask_lo'] ?? null),
                'details' => $this->valueOrFallback($publishedTrip?->details, $legacyTripDetails['details'] ?? null),
                'status' => $trip->status,
                'role' => $trip->role,
                'notes' => $trip->notes,
                'created_at' => $trip->created_at,
            ],
        ]);
    }

    public function browseTrips(Request $request)
    {
        $perPage = max(1, min((int) $request->integer('per_page', 20), 100));
        $page = max(1, (int) $request->integer('page', 1));
        $eligibleTrips = $this->swapService->getUserEligibleTrips($request->user());

        $items = $eligibleTrips->forPage($page, $perPage)->map(function ($trip) {
            $legacyTripDetails = $this->parseLegacyTripFieldsFromNotes($trip->notes);
            $departureDate = $trip->flight?->departure_date ? $trip->flight->departure_date->format('Y-m-d') : null;
            $arrivalDate = $trip->flight?->arrival_date ? $trip->flight->arrival_date->format('Y-m-d') : null;

            return [
                'id' => $trip->id,
                'user' => [
                    'id' => $trip->user->id,
                    'name' => $trip->user->full_name,
                    'employee_id' => $trip->user->employee_id,
                ],
                'flight' => [
                    'id' => $trip->flight?->id,
                    'number' => $trip->flight?->flight_number,
                    'departure' => $trip->flight?->departure_airport,
                    'arrival' => $trip->flight?->arrival_airport,
                    'departure_date' => $departureDate,
                    'arrival_date' => $arrivalDate,
                    'departure_time' => $trip->flight?->departure_time ? $trip->flight->departure_time->format('H:i:s') : null,
                    'arrival_time' => $trip->flight?->arrival_time ? $trip->flight->arrival_time->format('H:i:s') : null,
                    'status' => $trip->flight?->status,
                ],
                // Separate trip detail fields
                'flight_number' => $this->valueOrFallback($trip->flight_number, $trip->flight?->flight_number),
                'departure_date' => $departureDate,
                'arrival_date' => $arrivalDate,
                'legs' => $this->valueOrFallback($trip->legs, $legacyTripDetails['legs'] ?? null),
                'fly_type' => $this->valueOrFallback($trip->fly_type, $legacyTripDetails['fly_type'] ?? null),
                'report_time' => $this->valueOrFallback($trip->report_time, $legacyTripDetails['report_time'] ?? null),
                'offer_lo' => $this->valueOrFallback($this->parseOfferLo($trip->offer_lo), $legacyTripDetails['offer_lo'] ?? null),
                'ask_lo' => $this->valueOrFallback($this->parseAskLo($trip->ask_lo), $legacyTripDetails['ask_lo'] ?? null),
                'details' => $this->valueOrFallback($trip->details, $legacyTripDetails['details'] ?? null),
                'notes' => $trip->notes,
                'status' => $trip->status,
                'published_at' => $trip->published_at,
                'expires_at' => $trip->expires_at,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $items,
                'pagination' => [
                    'current_page' => $page,
                    'last_page' => max(1, (int) ceil($eligibleTrips->count() / $perPage)),
                    'per_page' => $perPage,
                    'total' => $eligibleTrips->count(),
                ],
            ],
        ]);
    }

    public function publishTrip(PublishTripRequest $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if (!$user->airline_id || !$user->plane_type_id) {
            return response()->json([
                'success' => false,
                'message' => 'Please complete your profile with airline and plane type before publishing a trip.',
            ], 422);
        }
        
        // Parse the date from request
        $departureDate = \Carbon\Carbon::parse($request->date);
        $arrivalDateInput = $request->input('arrival_date');
        if ($this->isBlankString($arrivalDateInput)) {
            $arrivalTimeAsDate = $request->input('arrival_time');
            if (is_string($arrivalTimeAsDate) && preg_match('/^\d{4}-\d{2}-\d{2}$/', trim($arrivalTimeAsDate)) === 1) {
                $arrivalDateInput = trim($arrivalTimeAsDate);
            }
        }
        $arrivalDate = $this->isBlankString($arrivalDateInput) ? null : \Carbon\Carbon::parse($arrivalDateInput);
        $departureTime = $this->normalizeFlightTime($request->input('departure_time'), '08:00:00');
        $arrivalTime = $this->normalizeFlightTime($request->input('arrival_time'), '10:30:00');
        $legacyTripDetails = $this->parseLegacyTripFieldsFromNotes($request->notes);
        $hasFlightArrivalDate = $this->hasColumn('flights', 'arrival_date');

        // Check columns OUTSIDE transaction to avoid locking issues
        $hasPublishedTripUserId = $this->hasColumn('published_trips', 'user_id');
        $hasPublishedTripFlightId = $this->hasColumn('published_trips', 'flight_id');
        $hasPublishedTripUserTripId = $this->hasColumn('published_trips', 'user_trip_id');
        $hasPublishedTripFlightNumber = $this->hasColumn('published_trips', 'flight_number');
        $hasPublishedTripLegs = $this->hasColumn('published_trips', 'legs');
        $hasPublishedTripFlyType = $this->hasColumn('published_trips', 'fly_type');
        $hasPublishedTripReportTime = $this->hasColumn('published_trips', 'report_time');
        $hasPublishedTripOfferLo = $this->hasColumn('published_trips', 'offer_lo');
        $hasPublishedTripAskLo = $this->hasColumn('published_trips', 'ask_lo');
        $hasPublishedTripDetails = $this->hasColumn('published_trips', 'details');
        $hasPublishedTripNotes = $this->hasColumn('published_trips', 'notes');

        try {
            [$flight, $userTrip, $publishedTrip] = DB::transaction(function () use ($request, $user, $departureDate, $arrivalDate, $departureTime, $arrivalTime, $legacyTripDetails, $hasFlightArrivalDate, $hasPublishedTripUserId, $hasPublishedTripFlightId, $hasPublishedTripUserTripId, $hasPublishedTripFlightNumber, $hasPublishedTripLegs, $hasPublishedTripFlyType, $hasPublishedTripReportTime, $hasPublishedTripOfferLo, $hasPublishedTripAskLo, $hasPublishedTripDetails, $hasPublishedTripNotes) {
                // flights.flight_number is unique, so update or create using that key.
                $flightUpdateData = [
                    'departure_airport' => $request->departure,
                    'arrival_airport' => $request->arrival,
                    'departure_date' => $departureDate->toDateString(),
                    'departure_time' => $departureTime,
                    'arrival_time' => $arrivalTime,
                    'airline_id' => $user->airline_id,
                    'plane_type_id' => $user->plane_type_id,
                    'status' => 'scheduled',
                ];

                if ($hasFlightArrivalDate) {
                    $flightUpdateData['arrival_date'] = $arrivalDate?->toDateString();
                }

                $flight = Flight::updateOrCreate(
                    [
                        'flight_number' => $request->flight_number,
                    ],
                    $flightUpdateData
                );

                // Ensure assignment exists for this user and flight.
                $userTripDefaults = [
                    'status' => 'assigned',
                ];

                $userTrip = UserTrip::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'flight_id' => $flight->id,
                    ],
                    $userTripDefaults
                );

                $publishedTripData = [
                    'status' => 'active',
                    'published_at' => now(),
                    'expires_at' => $request->expires_at ?? now()->addDays(7),
                ];

                if ($hasPublishedTripUserId) {
                    $publishedTripData['user_id'] = $user->id;
                }
                if ($hasPublishedTripFlightId) {
                    $publishedTripData['flight_id'] = $flight->id;
                }
                if ($hasPublishedTripUserTripId) {
                    $publishedTripData['user_trip_id'] = $userTrip->id;
                }
                if ($hasPublishedTripFlightNumber) {
                    $publishedTripData['flight_number'] = $request->flight_number;
                }
                if ($hasPublishedTripLegs) {
                    $publishedTripData['legs'] = $this->valueOrFallback($request->input('legs'), $legacyTripDetails['legs'] ?? null);
                }
                if ($hasPublishedTripFlyType) {
                    $publishedTripData['fly_type'] = $this->valueOrFallback($request->input('fly_type'), $legacyTripDetails['fly_type'] ?? null);
                }
                if ($hasPublishedTripReportTime) {
                    $publishedTripData['report_time'] = $this->valueOrFallback($request->input('report_time'), $legacyTripDetails['report_time'] ?? null);
                }
                if ($hasPublishedTripOfferLo) {
                    $publishedTripData['offer_lo'] = $this->serializeOfferLo($this->valueOrFallback($request->input('offer_lo'), $legacyTripDetails['offer_lo'] ?? null));
                }
                if ($hasPublishedTripAskLo) {
                    $publishedTripData['ask_lo'] = $this->serializeAskLo($this->valueOrFallback($request->input('ask_lo'), $legacyTripDetails['ask_lo'] ?? null));
                }
                if ($hasPublishedTripDetails) {
                    $publishedTripData['details'] = $this->valueOrFallback($request->input('details'), $legacyTripDetails['details'] ?? null);
                }
                if ($hasPublishedTripNotes) {
                    $publishedTripData['notes'] = $request->notes;
                }

                $publishedTrip = PublishedTrip::create($publishedTripData);

                return [$flight, $userTrip, $publishedTrip];
            });
        } catch (Throwable $exception) {
            Log::error('Failed to publish trip', [
                'user_id' => $user->id,
                'flight_number' => $request->flight_number,
                'error' => $exception->getMessage(),
                'exception' => $exception,
            ]);

            $response = [
                'success' => false,
                'message' => __('messages.server_error'),
            ];

            if (config('app.debug')) {
                $response['error'] = $exception->getMessage();
            }

            return response()->json($response, 500);
        }

        return response()->json([
            'success' => true,
            'message' => __('trips.trip_published'),
            'data' => [
                'id' => $publishedTrip->id,
                'flight' => [
                    'id' => $flight->id,
                    'number' => $flight->flight_number,
                    'departure' => $flight->departure_airport,
                    'arrival' => $flight->arrival_airport,
                    'departure_date' => $flight->departure_date->format('Y-m-d'),
                    'arrival_date' => $flight->arrival_date ? $flight->arrival_date->format('Y-m-d') : null,
                    'departure_time' => $flight->departure_time ? $flight->departure_time->format('H:i:s') : null,
                    'arrival_time' => $flight->arrival_time ? $flight->arrival_time->format('H:i:s') : null,
                ],
                'position' => $userTrip->role,
                'status' => 'available',
                'expires_at' => $publishedTrip->expires_at,
                'flight_number' => $publishedTrip->flight_number,
                'legs' => $publishedTrip->legs,
                'fly_type' => $publishedTrip->fly_type,
                'report_time' => $publishedTrip->report_time,
                'offer_lo' => $this->parseOfferLo($publishedTrip->offer_lo),
                'ask_lo' => $this->parseAskLo($publishedTrip->ask_lo),
                'details' => $publishedTrip->details,
                'notes' => $publishedTrip->notes,
            ],
        ], 201);
    }

    public function assignTripPosition(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $validated = $request->validate([
            'flight_number' => 'required|string|max:20',
            'departure' => 'required|string|size:3',
            'arrival' => 'required|string|size:3',
            'date' => 'required|date|after:today',
            'arrival_date' => 'nullable|date|after_or_equal:date',
            'departure_time' => 'nullable|string|max:50',
            'arrival_time' => 'nullable|string|max:50',
            'position' => 'required|string|in:Captain,First Officer,Purser,Flight Attendant',
            'notes' => 'nullable|string|max:500',
        ]);
        
        if (!$user->airline_id || !$user->plane_type_id) {
            return response()->json([
                'success' => false,
                'message' => 'Please complete your profile with airline and plane type before assigning a position.',
            ], 422);
        }

        $departureDate = \Carbon\Carbon::parse($validated['date']);
        $arrivalDateInput = array_key_exists('arrival_date', $validated) ? $validated['arrival_date'] : null;
        if ($this->isBlankString($arrivalDateInput) && array_key_exists('arrival_time', $validated)) {
            $arrivalTimeAsDate = $validated['arrival_time'];
            if (is_string($arrivalTimeAsDate) && preg_match('/^\d{4}-\d{2}-\d{2}$/', trim($arrivalTimeAsDate)) === 1) {
                $arrivalDateInput = trim($arrivalTimeAsDate);
            }
        }
        $arrivalDate = !$this->isBlankString($arrivalDateInput)
            ? \Carbon\Carbon::parse($arrivalDateInput)
            : null;
        $departureTime = $this->normalizeFlightTime($validated['departure_time'] ?? null, '08:00:00');
        $arrivalTime = $this->normalizeFlightTime($validated['arrival_time'] ?? null, '10:30:00');
        $hasFlightArrivalDate = $this->hasColumn('flights', 'arrival_date');
        $hasUserTripRole = $this->hasColumn('user_trips', 'role');
        $hasUserTripNotes = $this->hasColumn('user_trips', 'notes');

        try {
            [$flight, $userTrip] = DB::transaction(function () use ($validated, $user, $departureDate, $arrivalDate, $departureTime, $arrivalTime, $hasFlightArrivalDate, $hasUserTripRole, $hasUserTripNotes) {
                $flightUpdateData = [
                    'departure_airport' => $validated['departure'],
                    'arrival_airport' => $validated['arrival'],
                    'departure_date' => $departureDate->toDateString(),
                    'departure_time' => $departureTime,
                    'arrival_time' => $arrivalTime,
                    'airline_id' => $user->airline_id,
                    'plane_type_id' => $user->plane_type_id,
                    'status' => 'scheduled',
                ];

                if ($hasFlightArrivalDate) {
                    $flightUpdateData['arrival_date'] = $arrivalDate?->toDateString();
                }

                $flight = Flight::updateOrCreate(
                    [
                        'flight_number' => $validated['flight_number'],
                    ],
                    $flightUpdateData
                );

                $userTrip = UserTrip::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'flight_id' => $flight->id,
                    ],
                    [
                        'status' => 'assigned',
                    ]
                );

                $updates = [];

                if ($hasUserTripRole) {
                    $updates['role'] = $validated['position'];
                }

                if ($hasUserTripNotes) {
                    $updates['notes'] = $validated['notes'] ?? null;
                }

                if ($updates !== []) {
                    $userTrip->fill($updates);
                    if ($userTrip->isDirty()) {
                        $userTrip->save();
                    }
                }

                return [$flight, $userTrip];
            });
        } catch (Throwable $exception) {
            Log::error('Failed to assign trip position', [
                'user_id' => $user->id,
                'flight_number' => $validated['flight_number'],
                'error' => $exception->getMessage(),
                'exception' => $exception,
            ]);

            $response = [
                'success' => false,
                'message' => __('messages.server_error'),
            ];

            if (config('app.debug')) {
                $response['error'] = $exception->getMessage();
            }

            return response()->json($response, 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Trip position assigned successfully.',
            'data' => [
                'user_trip_id' => $userTrip->id,
                'position' => $userTrip->role,
                'notes' => $userTrip->notes,
                'flight' => [
                    'id' => $flight->id,
                    'number' => $flight->flight_number,
                    'departure' => $flight->departure_airport,
                    'arrival' => $flight->arrival_airport,
                    'departure_date' => $flight->departure_date->format('Y-m-d'),
                    'arrival_date' => $flight->arrival_date ? $flight->arrival_date->format('Y-m-d') : null,
                ],
            ],
        ]);
    }

    public function swapHistory(Request $request)
    {
        $user = $request->user();
        $perPage = max(1, min((int) $request->integer('per_page', 20), 100));

        $swapRequests = SwapRequest::where('requester_id', $user->id)
            ->orWhere('responder_id', $user->id)
            ->with(['requester', 'responder', 'publishedTrip.flight'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->through(function ($swap) use ($user) {
                $isRequester = $swap->requester_id === $user->id;
                $otherUser = $isRequester ? $swap->responder : $swap->requester;
                $legacyTripDetails = $this->parseLegacyTripFieldsFromNotes($swap->publishedTrip?->notes);
                $departureDate = $swap->publishedTrip?->flight?->departure_date
                    ? $swap->publishedTrip->flight->departure_date->format('Y-m-d')
                    : null;
                $arrivalDate = $swap->publishedTrip?->flight?->arrival_date
                    ? $swap->publishedTrip->flight->arrival_date->format('Y-m-d')
                    : null;
                
                return [
                    'id' => $swap->id,
                    'type' => $isRequester ? 'outgoing' : 'incoming',
                    'published_trip_id' => $swap->published_trip_id,
                    'other_user' => $otherUser ? [
                        'id' => $otherUser->id,
                        'name' => $otherUser->full_name,
                    ] : null,
                    'flight' => $swap->publishedTrip && $swap->publishedTrip->flight ? [
                        'id' => $swap->publishedTrip->flight->id,
                        'number' => $swap->publishedTrip->flight->flight_number,
                        'departure' => $swap->publishedTrip->flight->departure_airport,
                        'arrival' => $swap->publishedTrip->flight->arrival_airport,
                        'route' => $swap->publishedTrip->flight->departure_airport . ' → ' . 
                                  $swap->publishedTrip->flight->arrival_airport,
                        'departure_date' => $departureDate,
                        'arrival_date' => $arrivalDate,
                        'departure_time' => $swap->publishedTrip->flight->departure_time ?
                                  $swap->publishedTrip->flight->departure_time->format('H:i:s') : null,
                        'arrival_time' => $swap->publishedTrip->flight->arrival_time ?
                                  $swap->publishedTrip->flight->arrival_time->format('H:i:s') : null,
                        'status' => $swap->publishedTrip->flight->status,
                    ] : null,
                    'trip_details' => $swap->publishedTrip ? [
                        'flight_number' => $this->valueOrFallback($swap->publishedTrip->flight_number, $swap->publishedTrip->flight?->flight_number),
                        'departure_date' => $departureDate,
                        'arrival_date' => $arrivalDate,
                        'legs' => $this->valueOrFallback($swap->publishedTrip->legs, $legacyTripDetails['legs'] ?? null),
                        'fly_type' => $this->valueOrFallback($swap->publishedTrip->fly_type, $legacyTripDetails['fly_type'] ?? null),
                        'report_time' => $this->valueOrFallback($swap->publishedTrip->report_time, $legacyTripDetails['report_time'] ?? null),
                        'offer_lo' => $this->valueOrFallback($this->parseOfferLo($swap->publishedTrip->offer_lo), $legacyTripDetails['offer_lo'] ?? null),
                        'ask_lo' => $this->valueOrFallback($this->parseAskLo($swap->publishedTrip->ask_lo), $legacyTripDetails['ask_lo'] ?? null),
                        'details' => $this->valueOrFallback($swap->publishedTrip->details, $legacyTripDetails['details'] ?? null),
                        'notes' => $swap->publishedTrip->notes,
                        'published_at' => $swap->publishedTrip->published_at,
                        'expires_at' => $swap->publishedTrip->expires_at,
                    ] : null,
                    'status' => $swap->status,
                    'message' => $swap->message,
                    'responded_at' => $swap->responded_at,
                    'manager_approval_status' => $swap->manager_approval_status,
                    'created_at' => $swap->created_at,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $swapRequests->items(),
                'pagination' => [
                    'current_page' => $swapRequests->currentPage(),
                    'last_page' => $swapRequests->lastPage(),
                    'per_page' => $swapRequests->perPage(),
                    'total' => $swapRequests->total(),
                ],
            ],
        ]);
    }
}
