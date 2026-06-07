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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
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

    private function resolvePublishedTripImagePath(Request $request): ?string
    {
        $imageFile = $request->file('image') ?: $request->file('image_path');
        if ($imageFile) {
            return $imageFile->store('trip-images', 'public');
        }

        $imagePathInput = $request->input('image_path');
        if (!is_string($imagePathInput) || trim($imagePathInput) === '') {
            return null;
        }

        if (preg_match('/^https?:\/\//i', trim($imagePathInput))) {
            return $this->downloadRemoteImageToStorage(trim($imagePathInput)) ?? trim($imagePathInput);
        }

        return trim($imagePathInput);
    }

    private function downloadRemoteImageToStorage(string $url): ?string
    {
        try {
            $response = Http::timeout(10)->get($url);
            if (!$response->successful()) {
                return null;
            }

            $contentType = $response->header('Content-Type', '');
            if (!str_starts_with($contentType, 'image/')) {
                return null;
            }

            $extension = explode('/', $contentType)[1] ?? 'jpg';
            $extension = preg_replace('/[^a-z0-9]+/i', '', $extension) ?: 'jpg';
            $path = 'trip-images/' . uniqid('img_', true) . '.' . $extension;

            Storage::disk('public')->put($path, $response->body());
            return $path;
        } catch (Throwable $exception) {
            Log::warning('Failed to download remote image for published trip', [
                'url' => $url,
                'error' => $exception->getMessage(),
            ]);
            return null;
        }
    }

    private function validateLoField(string $attribute, mixed $value, \Closure $fail): void
    {
        if (is_string($value)) {
            if (mb_strlen($value) > 5000) {
                $fail('The ' . $attribute . ' field is too long.');
            }

            return;
        }

        if (!is_array($value)) {
            $fail('The ' . $attribute . ' field must be a string or list.');
            return;
        }

        foreach ($value as $index => $item) {
            if (!is_array($item)) {
                $fail('Each item in ' . $attribute . ' must be an object.');
                return;
            }

            $time = $item['time'] ?? null;
            $type = $item['type'] ?? ($item['string'] ?? null);

            $timeIsValid = false;
            if (is_string($time)) {
                $trimmedTime = trim($time);
                if (preg_match('/^\d+:[0-5]\d$/', $trimmedTime) || preg_match('/^\d+(?:\.\d+)?$/', $trimmedTime)) {
                    $timeIsValid = true;
                }
            } elseif (is_int($time) || is_float($time)) {
                if ($time >= 0) {
                    $timeIsValid = true;
                }
            }

            if (!$timeIsValid) {
                $fail('The ' . $attribute . '.' . $index . '.time must be a number or use H:MM format.');
                return;
            }

            if (!is_string($type) || trim($type) === '') {
                $fail('The ' . $attribute . '.' . $index . '.type (or .string) is required.');
                return;
            }
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

    private function normalizeInputValue(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value) && trim($value) === '') {
            return null;
        }

        if (is_array($value) && empty($value)) {
            return null;
        }

        return $value;
    }

    private function getRequestValue(Request $request, string $field, mixed $fallback = null): mixed
    {
        if (!$request->exists($field)) {
            return $fallback;
        }

        return $this->normalizeInputValue($request->input($field));
    }

    private function getUpdateRequestValue(Request $request, string $field, mixed $fallback = null): mixed
    {
        if (!$request->exists($field)) {
            return $fallback;
        }

        return $this->normalizeInputValue($request->input($field));
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

    private function buildFlightLookupCriteria(?string $flightNumber, ?string $departureDate, ?string $departureAirport, ?string $arrivalAirport): array
    {
        $criteria = [];

        if ($flightNumber !== null) {
            $criteria['flight_number'] = $flightNumber;
        }

        if ($departureDate !== null) {
            $criteria['departure_date'] = $departureDate;
        }

        if ($departureAirport !== null) {
            $criteria['departure_airport'] = $departureAirport;
        }

        if ($arrivalAirport !== null) {
            $criteria['arrival_airport'] = $arrivalAirport;
        }

        return $criteria;
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
                    'flight_number' => $this->valueOrFallback($trip->flight?->flight_number, $publishedTrip?->flight_number),
                    'departure_date' => $departureDate,
                    'arrival_date' => $arrivalDate,
                    'legs' => $this->valueOrFallback($publishedTrip?->legs, $legacyTripDetails['legs'] ?? null),
                    'fly_type' => $this->valueOrFallback($publishedTrip?->fly_type, $legacyTripDetails['fly_type'] ?? null),
                    'report_time' => $this->valueOrFallback($publishedTrip?->report_time, $legacyTripDetails['report_time'] ?? null),
                    'offer_lo' => $this->valueOrFallback($this->parseOfferLo($publishedTrip?->offer_lo), $legacyTripDetails['offer_lo'] ?? null),
                    'ask_lo' => $this->valueOrFallback($this->parseAskLo($publishedTrip?->ask_lo), $legacyTripDetails['ask_lo'] ?? null),
                    'details' => $this->valueOrFallback($publishedTrip?->details, $legacyTripDetails['details'] ?? null),
                    'image_url' => $publishedTrip?->image_path ? Storage::url($publishedTrip->image_path) : null,
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

    public function updateMyTrip(Request $request, UserTrip $trip)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if ($trip->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to update this trip.',
            ], 403);
        }

        $validated = $request->validate([
            'flight_number' => [
                'nullable',
                'string',
                'max:20',
            ],
            'departure' => 'nullable|string|min:2|max:3',
            'arrival' => 'nullable|string|min:2|max:3',
            'departure_date' => 'nullable|date',
            'arrival_date' => 'nullable|date',
            'departure_time' => 'nullable|string|max:50',
            'arrival_time' => 'nullable|string|max:50',
            'position' => 'nullable|string|in:Captain,First Officer,Purser,Flight Attendant',
            'legs' => 'nullable|integer|min:1',
            'fly_type' => 'nullable|string|max:50',
            'report_time' => 'nullable|string|max:50',
            'details' => 'nullable|string|max:1000',
            'offer_lo' => [
                'nullable',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $this->validateLoField($attribute, $value, $fail);
                },
            ],
            'ask_lo' => [
                'nullable',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $this->validateLoField($attribute, $value, $fail);
                },
            ],
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            DB::transaction(function () use ($trip, $validated) {
                $updateData = [];

                if ($validated['notes'] ?? null) {
                    $updateData['notes'] = $validated['notes'];
                }

                if ($validated['position'] ?? null) {
                    $updateData['role'] = $validated['position'];
                }

                if (!empty($updateData)) {
                    $trip->update($updateData);
                }

                // Update flight details if provided
                if ($trip->flight_id) {
                    $flight = $trip->flight;

                    if (!$flight) {
                        Log::warning('Trip update skipped flight update because related flight record is missing.', [
                            'trip_id' => $trip->id,
                            'flight_id' => $trip->flight_id,
                            'user_id' => $trip->user_id,
                        ]);
                        return;
                    }

                    $flightUpdateData = [];

                    if ($validated['flight_number'] ?? null) {
                        $flightUpdateData['flight_number'] = $validated['flight_number'];
                    }

                    if ($validated['departure_date'] ?? null) {
                        $flightUpdateData['departure_date'] = $validated['departure_date'];
                    }

                    if ($validated['arrival_date'] ?? null) {
                        $flightUpdateData['arrival_date'] = $validated['arrival_date'];
                    }

                    if ($validated['departure_time'] ?? null) {
                        $flightUpdateData['departure_time'] = $this->normalizeFlightTime($validated['departure_time'], '08:00:00');
                    }

                    if ($validated['arrival_time'] ?? null) {
                        $flightUpdateData['arrival_time'] = $this->normalizeFlightTime($validated['arrival_time'], '10:30:00');
                    }

                    if ($validated['departure'] ?? null) {
                        $flightUpdateData['departure_airport'] = $validated['departure'];
                    }

                    if ($validated['arrival'] ?? null) {
                        $flightUpdateData['arrival_airport'] = $validated['arrival'];
                    }

                    if (!empty($flightUpdateData)) {
                        $flight->update($flightUpdateData);
                    }

                    $publishedTrip = $trip->publishedTrips()->latest('id')->first();
                    if ($publishedTrip) {
                        $publishedTripUpdate = [];

                        if (array_key_exists('flight_number', $validated)) {
                            $publishedTripUpdate['flight_number'] = $validated['flight_number'];
                        }
                        if (array_key_exists('legs', $validated)) {
                            $publishedTripUpdate['legs'] = $validated['legs'];
                        }
                        if (array_key_exists('fly_type', $validated)) {
                            $publishedTripUpdate['fly_type'] = $validated['fly_type'];
                        }
                        if (array_key_exists('report_time', $validated)) {
                            $publishedTripUpdate['report_time'] = $validated['report_time'];
                        }
                        if (array_key_exists('details', $validated)) {
                            $publishedTripUpdate['details'] = $validated['details'];
                        }
                        if (array_key_exists('offer_lo', $validated)) {
                            $publishedTripUpdate['offer_lo'] = $this->serializeOfferLo($validated['offer_lo']);
                        }
                        if (array_key_exists('ask_lo', $validated)) {
                            $publishedTripUpdate['ask_lo'] = $this->serializeAskLo($validated['ask_lo']);
                        }

                        if (!empty($publishedTripUpdate)) {
                            $publishedTrip->update($publishedTripUpdate);
                        }
                    }
                }
            });

            $trip->load([
                'flight' => function ($query) {
                    $query->with(['airline', 'planeType']);
                },
                'publishedTrips' => function ($query) {
                    $query->latest('id');
                },
            ]);

            $publishedTrip = $trip->publishedTrips->first();

            return response()->json([
                'success' => true,
                'message' => 'Trip updated successfully.',
                'data' => [
                    'id' => $trip->id,
                    'flight_number' => $trip->flight?->flight_number,
                    'departure' => $trip->flight?->departure_airport,
                    'arrival' => $trip->flight?->arrival_airport,
                    'departure_date' => $trip->flight?->departure_date ? $trip->flight->departure_date->format('Y-m-d') : null,
                    'arrival_date' => $trip->flight?->arrival_date ? $trip->flight->arrival_date->format('Y-m-d') : null,
                    'departure_time' => $trip->flight?->departure_time,
                    'arrival_time' => $trip->flight?->arrival_time,
                    'position' => $trip->role,
                    'notes' => $trip->notes,
                    'legs' => $publishedTrip?->legs,
                    'fly_type' => $publishedTrip?->fly_type,
                    'report_time' => $publishedTrip?->report_time,
                    'details' => $publishedTrip?->details,
                    'offer_lo' => $this->parseOfferLo($publishedTrip?->offer_lo),
                    'ask_lo' => $this->parseAskLo($publishedTrip?->ask_lo),
                ],
            ]);
        } catch (Throwable $exception) {
            Log::error('Failed to update trip', [
                'user_id' => $user->id,
                'trip_id' => $trip->id,
                'error' => $exception->getMessage(),
            ]);

            $status = 500;
            $message = 'Failed to update trip.';

            if ($exception instanceof \Illuminate\Database\QueryException) {
                $sqlState = $exception->errorInfo[0] ?? null;
                if ($sqlState === '23000' || $sqlState === '23505') {
                    $status = 422;
                    $message = 'The flight number is already in use. Please choose another.';
                }
            }

            return response()->json([
                'success' => false,
                'message' => $message,
            ], $status);
        }
    }

    public function destroyMyTrip(Request $request, UserTrip $trip)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if ($trip->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to delete this trip.',
            ], 403);
        }

        try {
            $tripId = $trip->id;

            DB::transaction(function () use ($trip) {
                // Delete related published trips
                PublishedTrip::where('user_trip_id', $trip->id)->delete();
                // Delete the trip itself
                $trip->delete();
            });

            return response()->json([
                'success' => true,
                'message' => 'Trip deleted successfully.',
                'data' => [
                    'id' => $tripId,
                ],
            ]);
        } catch (Throwable $exception) {
            Log::error('Failed to delete trip', [
                'user_id' => $user->id,
                'trip_id' => $trip->id,
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete trip.',
            ], 500);
        }
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
                'flight_number' => $this->valueOrFallback($trip->flight?->flight_number, $publishedTrip?->flight_number),
                'departure_date' => $departureDate,
                'arrival_date' => $arrivalDate,
                'legs' => $this->valueOrFallback($publishedTrip?->legs, $legacyTripDetails['legs'] ?? null),
                'fly_type' => $this->valueOrFallback($publishedTrip?->fly_type, $legacyTripDetails['fly_type'] ?? null),
                'report_time' => $this->valueOrFallback($publishedTrip?->report_time, $legacyTripDetails['report_time'] ?? null),
                'offer_lo' => $this->valueOrFallback($this->parseOfferLo($publishedTrip?->offer_lo), $legacyTripDetails['offer_lo'] ?? null),
                'ask_lo' => $this->valueOrFallback($this->parseAskLo($publishedTrip?->ask_lo), $legacyTripDetails['ask_lo'] ?? null),
                'details' => $this->valueOrFallback($publishedTrip?->details, $legacyTripDetails['details'] ?? null),
                'image_url' => $publishedTrip?->image_path ? Storage::url($publishedTrip->image_path) : null,
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
        try {
            $eligibleTrips = $this->swapService->getUserEligibleTrips($request->user());

            $items = $eligibleTrips->forPage($page, $perPage)->map(function ($trip) {
            $legacyTripDetails = $this->parseLegacyTripFieldsFromNotes($trip->notes);
            $departureDate = $trip->flight?->departure_date ? $trip->flight->departure_date->format('Y-m-d') : null;
            $arrivalDate = $trip->flight?->arrival_date ? $trip->flight->arrival_date->format('Y-m-d') : null;

            return [
                'id' => $trip->id,
                'user' => [
                    'id' => $trip->user?->id,
                    'name' => $trip->user?->full_name,
                    'employee_id' => $trip->user?->employee_id,
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
                'image_url' => $trip->image_path ? Storage::url($trip->image_path) : null,
                'notes' => $trip->notes,
                'status' => $trip->status,
                'published_at' => $trip->published_at,
                'expires_at' => $trip->expires_at,
            ];
        })->values();
        } catch (Throwable $e) {
            Log::error('Failed to browse trips', [
                'user_id' => $request->user()?->id,
                'page' => $page,
                'per_page' => $perPage,
                'error' => $e->getMessage(),
                'exception' => $e,
            ]);

            return response()->json([
                'success' => false,
                'message' => __('messages.server_error'),
            ], 500);
        }

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

        // Check if trip_id is provided (publish existing trip) or full trip data is provided (create new trip)
        $tripId = $request->input('trip_id');

        if ($tripId) {
            // Publishing an existing UserTrip
            return $this->publishExistingTrip($request, $user, $tripId);
        }

        // Parse the departure date from request. Prefer departure_date over date.
        $departureDateInput = $request->input('departure_date', $request->input('date'));
        try {
            $departureDate = \Carbon\Carbon::parse($departureDateInput);
        } catch (Throwable $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid departure date format. Use YYYY-MM-DD.',
            ], 422);
        }

        $flightNumber = $this->isBlankString($request->input('flight_number')) ? null : trim($request->input('flight_number'));
        $arrivalDateInput = $request->input('arrival_date');
        if ($this->isBlankString($arrivalDateInput)) {
            $arrivalTimeAsDate = $request->input('arrival_time');
            if (is_string($arrivalTimeAsDate) && preg_match('/^\d{4}-\d{2}-\d{2}$/', trim($arrivalTimeAsDate)) === 1) {
                $arrivalDateInput = trim($arrivalTimeAsDate);
            }
        }

        try {
            $arrivalDate = $this->isBlankString($arrivalDateInput) ? null : \Carbon\Carbon::parse($arrivalDateInput);
        } catch (Throwable $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid arrival date format. Use YYYY-MM-DD.',
            ], 422);
        }

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
        $hasPublishedTripImage = $this->hasColumn('published_trips', 'image_path');

        try {
            [$flight, $userTrip, $publishedTrip] = DB::transaction(function () use ($request, $user, $departureDate, $arrivalDate, $departureTime, $arrivalTime, $legacyTripDetails, $hasFlightArrivalDate, $hasPublishedTripUserId, $hasPublishedTripFlightId, $hasPublishedTripUserTripId, $hasPublishedTripFlightNumber, $hasPublishedTripLegs, $hasPublishedTripFlyType, $hasPublishedTripReportTime, $hasPublishedTripOfferLo, $hasPublishedTripAskLo, $hasPublishedTripDetails, $hasPublishedTripNotes, $hasPublishedTripImage, $flightNumber) {
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

                if ($flightNumber !== null) {
                    $flight = Flight::updateOrCreate(
                        $this->buildFlightLookupCriteria(
                            $flightNumber,
                            $departureDate->toDateString(),
                            $request->departure,
                            $request->arrival
                        ),
                        $flightUpdateData
                    );
                } else {
                    $flight = Flight::create([
                        'flight_number' => 'NOFL-' . strtoupper(Str::random(10)),
                    ] + $flightUpdateData);
                }

                // Create assignment for this user and flight (allow duplicates)
                try {
                    $userTrip = UserTrip::create([
                        'user_id' => $user->id,
                        'flight_id' => $flight->id,
                        'status' => 'assigned',
                    ]);
                } catch (\Illuminate\Database\QueryException $e) {
                    // If unique constraint violation, use firstOrCreate as fallback
                    if (strpos($e->getMessage(), '1062') !== false || $e->getCode() == '23000') {
                        $userTrip = UserTrip::firstOrCreate(
                            [
                                'user_id' => $user->id,
                                'flight_id' => $flight->id,
                            ],
                            ['status' => 'assigned']
                        );
                    } else {
                        throw $e;
                    }
                }

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
                    $publishedTripData['flight_number'] = $flight->flight_number;
                }
                if ($hasPublishedTripLegs) {
                    $publishedTripData['legs'] = $this->getRequestValue($request, 'legs', $legacyTripDetails['legs'] ?? null);
                }
                if ($hasPublishedTripFlyType) {
                    $publishedTripData['fly_type'] = $this->getRequestValue($request, 'fly_type', $legacyTripDetails['fly_type'] ?? null);
                }
                if ($hasPublishedTripReportTime) {
                    $publishedTripData['report_time'] = $this->getRequestValue($request, 'report_time', $legacyTripDetails['report_time'] ?? null);
                }
                if ($hasPublishedTripOfferLo) {
                    $publishedTripData['offer_lo'] = $this->serializeOfferLo($this->getRequestValue($request, 'offer_lo', $legacyTripDetails['offer_lo'] ?? null));
                }
                if ($hasPublishedTripAskLo) {
                    $publishedTripData['ask_lo'] = $this->serializeAskLo($this->getRequestValue($request, 'ask_lo', $legacyTripDetails['ask_lo'] ?? null));
                }
                if ($hasPublishedTripDetails) {
                    $publishedTripData['details'] = $this->getRequestValue($request, 'details', $legacyTripDetails['details'] ?? null);
                }
                if ($hasPublishedTripNotes) {
                    $publishedTripData['notes'] = $this->getRequestValue($request, 'notes', $request->notes);
                }

                if ($hasPublishedTripImage) {
                    $storedImagePath = $this->resolvePublishedTripImagePath($request);
                    if ($storedImagePath !== null) {
                        $publishedTripData['image_path'] = $storedImagePath;
                    }
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
                'flight_number' => $flight->flight_number,
                'legs' => $publishedTrip->legs,
                'fly_type' => $publishedTrip->fly_type,
                'report_time' => $publishedTrip->report_time,
                'offer_lo' => $this->parseOfferLo($publishedTrip->offer_lo),
                'ask_lo' => $this->parseAskLo($publishedTrip->ask_lo),
                'details' => $publishedTrip->details,
                'image_url' => $publishedTrip->image_path ? Storage::url($publishedTrip->image_path) : null,
                'notes' => $publishedTrip->notes,
            ],
        ], 201);
    }

    public function updatePublishedTrip(PublishTripRequest $request, PublishedTrip $publishedTrip)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if ($publishedTrip->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to update this published trip.',
            ], 403);
        }

        if (!$publishedTrip->isAvailable()) {
            return response()->json([
                'success' => false,
                'message' => 'Only active or available published trips can be updated.',
            ], 422);
        }

        $existingFlight = $publishedTrip->flight;

        $departureDate = $existingFlight?->departure_date;
        if ($request->exists('departure_date') || $request->exists('date')) {
            $departureDateInput = $request->input('departure_date', $request->input('date'));
            try {
                $departureDate = \Carbon\Carbon::parse($departureDateInput);
            } catch (Throwable $exception) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid departure date format. Use YYYY-MM-DD.',
                ], 422);
            }
        }

        $flightNumber = $this->isBlankString($request->input('flight_number')) ? null : trim($request->input('flight_number'));
        $arrivalDateInput = $request->input('arrival_date');
        if ($this->isBlankString($arrivalDateInput)) {
            $arrivalTimeAsDate = $request->input('arrival_time');
            if (is_string($arrivalTimeAsDate) && preg_match('/^\d{4}-\d{2}-\d{2}$/', trim($arrivalTimeAsDate)) === 1) {
                $arrivalDateInput = trim($arrivalTimeAsDate);
            }
        }

        $arrivalDate = $existingFlight?->arrival_date;
        if ($request->exists('arrival_date') || $request->exists('arrival_time') && is_string($arrivalDateInput) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $arrivalDateInput) === 1) {
            try {
                $arrivalDate = $this->isBlankString($arrivalDateInput) ? null : \Carbon\Carbon::parse($arrivalDateInput);
            } catch (Throwable $exception) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid arrival date format. Use YYYY-MM-DD.',
                ], 422);
            }
        }

        $departureTime = $request->exists('departure_time')
            ? $this->normalizeFlightTime($request->input('departure_time'), $existingFlight?->departure_time?->format('H:i:s') ?? '08:00:00')
            : ($existingFlight?->departure_time?->format('H:i:s') ?? '08:00:00');
        $arrivalTime = $request->exists('arrival_time')
            ? $this->normalizeFlightTime($request->input('arrival_time'), $existingFlight?->arrival_time?->format('H:i:s') ?? '10:30:00')
            : ($existingFlight?->arrival_time?->format('H:i:s') ?? '10:30:00');
        $legacyTripDetails = $this->parseLegacyTripFieldsFromNotes($publishedTrip->notes);
        $hasFlightArrivalDate = $this->hasColumn('flights', 'arrival_date');

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
        $hasPublishedTripImage = $this->hasColumn('published_trips', 'image_path');

        try {
            [$flight, $userTrip] = DB::transaction(function () use ($request, $user, $departureDate, $arrivalDate, $departureTime, $arrivalTime, $legacyTripDetails, $hasFlightArrivalDate, $hasPublishedTripFlightId, $hasPublishedTripUserTripId, $hasPublishedTripFlightNumber, $hasPublishedTripLegs, $hasPublishedTripFlyType, $hasPublishedTripReportTime, $hasPublishedTripOfferLo, $hasPublishedTripAskLo, $hasPublishedTripDetails, $hasPublishedTripNotes, $hasPublishedTripImage, $flightNumber, $publishedTrip, $existingFlight) {
                $flightUpdateData = [
                    'departure_airport' => $this->getUpdateRequestValue($request, 'departure', $existingFlight?->departure_airport),
                    'arrival_airport' => $this->getUpdateRequestValue($request, 'arrival', $existingFlight?->arrival_airport),
                    'departure_date' => $departureDate?->toDateString(),
                    'departure_time' => $departureTime,
                    'airline_id' => $user->airline_id,
                    'plane_type_id' => $user->plane_type_id,
                    'status' => 'scheduled',
                ];

                if ($hasFlightArrivalDate) {
                    $flightUpdateData['arrival_date'] = $arrivalDate?->toDateString();
                }

                if ($flightNumber !== null) {
                    $flight = Flight::updateOrCreate(
                        $this->buildFlightLookupCriteria(
                            $flightNumber,
                            $departureDate?->toDateString(),
                            $flightUpdateData['departure_airport'],
                            $flightUpdateData['arrival_airport']
                        ),
                        $flightUpdateData
                    );
                } elseif ($existingFlight) {
                    $existingFlight->update($flightUpdateData);
                    $flight = $existingFlight;
                } else {
                    $flight = Flight::create([
                        'flight_number' => 'NOFL-' . strtoupper(Str::random(10)),
                    ] + $flightUpdateData);
                }

                // Create assignment for this user and flight (allow duplicates)
                try {
                    $userTrip = UserTrip::create([
                        'user_id' => $user->id,
                        'flight_id' => $flight->id,
                        'status' => 'assigned',
                    ]);
                } catch (\Illuminate\Database\QueryException $e) {
                    // If unique constraint violation, use firstOrCreate as fallback
                    if (strpos($e->getMessage(), '1062') !== false || $e->getCode() == '23000') {
                        $userTrip = UserTrip::firstOrCreate(
                            [
                                'user_id' => $user->id,
                                'flight_id' => $flight->id,
                            ],
                            ['status' => 'assigned']
                        );
                    } else {
                        throw $e;
                    }
                }

                $publishedTripData = [
                    'status' => $publishedTrip->status,
                    'published_at' => $publishedTrip->published_at ?? now(),
                    'expires_at' => $request->expires_at ?? $publishedTrip->expires_at ?? now()->addDays(7),
                ];

                if ($hasPublishedTripFlightId) {
                    $publishedTripData['flight_id'] = $flight->id;
                }
                if ($hasPublishedTripUserTripId) {
                    $publishedTripData['user_trip_id'] = $userTrip->id;
                }
                if ($hasPublishedTripFlightNumber) {
                    $publishedTripData['flight_number'] = $flight->flight_number;
                }
                if ($hasPublishedTripLegs) {
                    $publishedTripData['legs'] = $this->getUpdateRequestValue($request, 'legs', $publishedTrip->legs ?? $legacyTripDetails['legs'] ?? null);
                }
                if ($hasPublishedTripFlyType) {
                    $publishedTripData['fly_type'] = $this->getUpdateRequestValue($request, 'fly_type', $publishedTrip->fly_type ?? $legacyTripDetails['fly_type'] ?? null);
                }
                if ($hasPublishedTripReportTime) {
                    $publishedTripData['report_time'] = $this->getUpdateRequestValue($request, 'report_time', $publishedTrip->report_time ?? $legacyTripDetails['report_time'] ?? null);
                }
                if ($hasPublishedTripOfferLo) {
                    $publishedTripData['offer_lo'] = $this->serializeOfferLo($this->getUpdateRequestValue($request, 'offer_lo', $publishedTrip->offer_lo ?? $legacyTripDetails['offer_lo'] ?? null));
                }
                if ($hasPublishedTripAskLo) {
                    $publishedTripData['ask_lo'] = $this->serializeAskLo($this->getUpdateRequestValue($request, 'ask_lo', $publishedTrip->ask_lo ?? $legacyTripDetails['ask_lo'] ?? null));
                }
                if ($hasPublishedTripDetails) {
                    $publishedTripData['details'] = $this->getUpdateRequestValue($request, 'details', $publishedTrip->details ?? $legacyTripDetails['details'] ?? null);
                }
                if ($hasPublishedTripNotes) {
                    $publishedTripData['notes'] = $this->getUpdateRequestValue($request, 'notes', $publishedTrip->notes);
                }

                if ($hasPublishedTripImage) {
                    $storedImagePath = $this->resolvePublishedTripImagePath($request);
                    if ($storedImagePath !== null) {
                        $publishedTripData['image_path'] = $storedImagePath;
                    }
                }

                $publishedTrip->update($publishedTripData);

                return [$flight, $userTrip];
            });
        } catch (Throwable $exception) {
            Log::error('Failed to update published trip', [
                'user_id' => $user->id,
                'published_trip_id' => $publishedTrip->id,
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
            'message' => 'Published trip updated successfully.',
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
                'status' => $publishedTrip->status,
                'expires_at' => $publishedTrip->expires_at,
                'flight_number' => $flight->flight_number,
                'legs' => $publishedTrip->legs,
                'fly_type' => $publishedTrip->fly_type,
                'report_time' => $publishedTrip->report_time,
                'offer_lo' => $this->parseOfferLo($publishedTrip->offer_lo),
                'ask_lo' => $this->parseAskLo($publishedTrip->ask_lo),
                'details' => $publishedTrip->details,
                'image_url' => $publishedTrip->image_path ? Storage::url($publishedTrip->image_path) : null,
                'notes' => $publishedTrip->notes,
            ],
        ]);
    }

    public function destroyPublishedTrip(Request $request, PublishedTrip $publishedTrip)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if ($publishedTrip->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to delete this published trip.',
            ], 403);
        }

        if (!$publishedTrip->isAvailable()) {
            return response()->json([
                'success' => false,
                'message' => 'Only active or available published trips can be canceled.',
            ], 422);
        }

        $publishedTrip->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'message' => 'Published trip canceled successfully.',
            'data' => [
                'id' => $publishedTrip->id,
                'status' => $publishedTrip->status,
            ],
        ]);
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

        $request->merge([
            'arrival_date' => $request->input('arrival_date', $request->input('arrivalDate', $request->input('arrival date'))),
            'date' => $request->input('date', $request->input('departure_date', $request->input('departureDate', $request->input('departure date')))),
            'departure_time' => $request->input('departure_time', $request->input('departureTime', $request->input('departure time'))),
            'arrival_time' => $request->input('arrival_time', $request->input('arrivalTime', $request->input('arrival time'))),
        ]);

        $validated = $request->validate([
            'flight_number' => 'required|string|max:20',
            'departure' => 'required|string|min:2|max:3',
            'arrival' => 'required|string|min:2|max:3',
            'date' => 'required|date|after_or_equal:today',
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

        try {
            $departureDate = \Carbon\Carbon::parse($validated['date']);
        } catch (Throwable $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid departure date format. Use YYYY-MM-DD.',
            ], 422);
        }

        $arrivalDateInput = array_key_exists('arrival_date', $validated) ? $validated['arrival_date'] : null;
        if ($this->isBlankString($arrivalDateInput) && array_key_exists('arrival_time', $validated)) {
            $arrivalTimeAsDate = $validated['arrival_time'];
            if (is_string($arrivalTimeAsDate) && preg_match('/^\d{4}-\d{2}-\d{2}$/', trim($arrivalTimeAsDate)) === 1) {
                $arrivalDateInput = trim($arrivalTimeAsDate);
            }
        }

        try {
            $arrivalDate = !$this->isBlankString($arrivalDateInput)
                ? \Carbon\Carbon::parse($arrivalDateInput)
                : null;
        } catch (Throwable $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid arrival date format. Use YYYY-MM-DD.',
            ], 422);
        }

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

                // Create assignment for this user and flight (allow duplicates)
                $userTripData = [
                    'user_id' => $user->id,
                    'flight_id' => $flight->id,
                    'status' => 'assigned',
                ];

                if ($hasUserTripRole) {
                    $userTripData['role'] = $validated['position'];
                }
                if ($hasUserTripNotes) {
                    $userTripData['notes'] = $validated['notes'] ?? null;
                }

                try {
                    $userTrip = UserTrip::create($userTripData);
                } catch (\Illuminate\Database\QueryException $e) {
                    // If unique constraint violation, use firstOrCreate as fallback
                    if (strpos($e->getMessage(), '1062') !== false || $e->getCode() == '23000') {
                        $userTrip = UserTrip::firstOrCreate(
                            [
                                'user_id' => $user->id,
                                'flight_id' => $flight->id,
                            ],
                            ['status' => 'assigned']
                        );
                        // Update the other fields if needed
                        if ($hasUserTripRole || $hasUserTripNotes) {
                            $userTrip->update(array_filter($userTripData, fn($k) => $k !== 'user_id' && $k !== 'flight_id' && $k !== 'status', ARRAY_FILTER_USE_KEY));
                        }
                    } else {
                        throw $e;
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
                        'flight_number' => $this->valueOrFallback($swap->publishedTrip->flight?->flight_number, $swap->publishedTrip->flight_number),
                        'departure_date' => $departureDate,
                        'arrival_date' => $arrivalDate,
                        'legs' => $this->valueOrFallback($swap->publishedTrip->legs, $legacyTripDetails['legs'] ?? null),
                        'fly_type' => $this->valueOrFallback($swap->publishedTrip->fly_type, $legacyTripDetails['fly_type'] ?? null),
                        'report_time' => $this->valueOrFallback($swap->publishedTrip->report_time, $legacyTripDetails['report_time'] ?? null),
                        'offer_lo' => $this->valueOrFallback($this->parseOfferLo($swap->publishedTrip->offer_lo), $legacyTripDetails['offer_lo'] ?? null),
                        'ask_lo' => $this->valueOrFallback($this->parseAskLo($swap->publishedTrip->ask_lo), $legacyTripDetails['ask_lo'] ?? null),
                        'details' => $this->valueOrFallback($swap->publishedTrip->details, $legacyTripDetails['details'] ?? null),
                        'image_url' => $swap->publishedTrip?->image_path ? Storage::url($swap->publishedTrip->image_path) : null,
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

    /**
     * Publish an existing UserTrip to the marketplace with optional image upload
     */
    private function publishExistingTrip(PublishTripRequest $request, $user, $tripId)
    {
        $userTrip = UserTrip::with('flight')->find($tripId);

        if (!$userTrip || $userTrip->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Trip not found or unauthorized.',
            ], 404);
        }

        $flight = $userTrip->flight;
        if (!$flight) {
            return response()->json([
                'success' => false,
                'message' => 'Trip has no associated flight.',
            ], 422);
        }

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
        $hasPublishedTripImage = $this->hasColumn('published_trips', 'image_path');

        try {
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
                $publishedTripData['flight_number'] = $flight->flight_number;
            }
            if ($hasPublishedTripLegs) {
                $publishedTripData['legs'] = $request->legs ?? $userTrip->legs;
            }
            if ($hasPublishedTripFlyType) {
                $publishedTripData['fly_type'] = $request->fly_type ?? $userTrip->fly_type;
            }
            if ($hasPublishedTripReportTime) {
                $publishedTripData['report_time'] = $request->report_time ?? $userTrip->report_time;
            }
            if ($hasPublishedTripOfferLo) {
                $publishedTripData['offer_lo'] = $this->serializeOfferLo($request->offer_lo ?? $userTrip->offer_lo);
            }
            if ($hasPublishedTripAskLo) {
                $publishedTripData['ask_lo'] = $this->serializeAskLo($request->ask_lo ?? $userTrip->ask_lo);
            }
            if ($hasPublishedTripDetails) {
                $publishedTripData['details'] = $request->details ?? $userTrip->details;
            }
            if ($hasPublishedTripNotes) {
                $publishedTripData['notes'] = $request->notes ?? $userTrip->notes;
            }

            // Handle image upload (accepts both 'image' and 'image_path' field names)
            if ($hasPublishedTripImage) {
                $storedImagePath = $this->resolvePublishedTripImagePath($request);
                if ($storedImagePath !== null) {
                    $publishedTripData['image_path'] = $storedImagePath;
                }
            }

            $publishedTrip = PublishedTrip::create($publishedTripData);

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
                    'flight_number' => $flight->flight_number,
                    'legs' => $publishedTrip->legs,
                    'fly_type' => $publishedTrip->fly_type,
                    'report_time' => $publishedTrip->report_time,
                    'offer_lo' => $this->parseOfferLo($publishedTrip->offer_lo),
                    'ask_lo' => $this->parseAskLo($publishedTrip->ask_lo),
                    'details' => $publishedTrip->details,
                    'image_url' => $publishedTrip->image_path ? Storage::url($publishedTrip->image_path) : null,
                    'notes' => $publishedTrip->notes,
                ],
            ], 201);
        } catch (Throwable $exception) {
            Log::error('Failed to publish existing trip', [
                'user_id' => $user->id,
                'trip_id' => $tripId,
                'error' => $exception->getMessage(),
                'exception' => $exception,
            ]);

            return response()->json([
                'success' => false,
                'message' => __('messages.server_error'),
                'error' => config('app.debug') ? $exception->getMessage() : null,
            ], 500);
        }
    }
}

