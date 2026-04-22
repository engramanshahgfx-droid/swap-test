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

        if (preg_match('/^([01]\\d|2[0-3]):[0-5]\\d(:[0-5]\\d)?$/', $normalized) === 1) {
            return strlen($normalized) === 5 ? $normalized . ':00' : $normalized;
        }

        try {
            return \Carbon\Carbon::parse($normalized)->format('H:i:s');
        } catch (\Throwable) {
            return $default;
        }
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
            }])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->through(function ($trip) {
                return [
                    'id' => $trip->id,
                    'flight' => [
                        'id' => $trip->flight->id,
                        'number' => $trip->flight->flight_number,
                        'departure' => $trip->flight->departure_airport,
                        'arrival' => $trip->flight->arrival_airport,
                        'date' => $trip->flight->date ? $trip->flight->date->format('Y-m-d') : null,
                        'time' => $trip->flight->time ? $trip->flight->time->format('H:i') : null,
                        'duration' => $trip->flight->formatted_duration,
                        'type' => $trip->flight->trip_type,
                    ],
                    'status' => $trip->status,
                    'role' => $trip->role,
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
        $trip = UserTrip::with(['flight', 'user', 'flight.airline', 'flight.planeType'])
            ->findOrFail($id);

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
                    'id' => $trip->flight->id,
                    'number' => $trip->flight->flight_number,
                    'departure' => $trip->flight->departure_airport,
                    'arrival' => $trip->flight->arrival_airport,
                    'date' => $trip->flight->date ? $trip->flight->date->format('Y-m-d') : null,
                    'time' => $trip->flight->time ? $trip->flight->time->format('H:i') : null,
                    'duration' => $trip->flight->formatted_duration,
                    'type' => $trip->flight->trip_type,
                    'airline' => $trip->flight->airline->name,
                    'plane_type' => $trip->flight->planeType->name,
                ],
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
            return [
                'id' => $trip->id,
                'user' => [
                    'id' => $trip->user->id,
                    'name' => $trip->user->full_name,
                    'employee_id' => $trip->user->employee_id,
                ],
                'flight' => [
                    'id' => $trip->flight->id,
                    'number' => $trip->flight->flight_number,
                    'departure' => $trip->flight->departure_airport,
                    'arrival' => $trip->flight->arrival_airport,
                    'date' => $trip->flight->departure_date ? $trip->flight->departure_date->format('Y-m-d') : null,
                ],
                // Separate trip detail fields
                'flight_number' => $trip->flight_number,
                'legs' => $trip->legs,
                'fly_type' => $trip->fly_type,
                'report_time' => $trip->report_time,
                'offer_lo' => $this->parseOfferLo($trip->offer_lo),
                'ask_lo' => $this->parseAskLo($trip->ask_lo),
                'details' => $trip->details,
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
        $departureTime = $this->normalizeFlightTime($request->input('departure_time'), '08:00:00');
        $arrivalTime = $this->normalizeFlightTime($request->input('arrival_time'), '10:30:00');

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
            [$flight, $userTrip, $publishedTrip] = DB::transaction(function () use ($request, $user, $departureDate, $departureTime, $arrivalTime, $hasPublishedTripUserId, $hasPublishedTripFlightId, $hasPublishedTripUserTripId, $hasPublishedTripFlightNumber, $hasPublishedTripLegs, $hasPublishedTripFlyType, $hasPublishedTripReportTime, $hasPublishedTripOfferLo, $hasPublishedTripAskLo, $hasPublishedTripDetails, $hasPublishedTripNotes) {
                // flights.flight_number is unique, so update or create using that key.
                $flight = Flight::updateOrCreate(
                    [
                        'flight_number' => $request->flight_number,
                    ],
                    [
                        'departure_airport' => $request->departure,
                        'arrival_airport' => $request->arrival,
                        'departure_date' => $departureDate->toDateString(),
                        'departure_time' => $departureTime,
                        'arrival_time' => $arrivalTime,
                        'airline_id' => $user->airline_id,
                        'plane_type_id' => $user->plane_type_id,
                        'status' => 'scheduled',
                    ]
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
                    $publishedTripData['legs'] = $request->legs;
                }
                if ($hasPublishedTripFlyType) {
                    $publishedTripData['fly_type'] = $request->fly_type;
                }
                if ($hasPublishedTripReportTime) {
                    $publishedTripData['report_time'] = $request->report_time;
                }
                if ($hasPublishedTripOfferLo) {
                    $publishedTripData['offer_lo'] = $this->serializeOfferLo($request->input('offer_lo'));
                }
                if ($hasPublishedTripAskLo) {
                    $publishedTripData['ask_lo'] = $this->serializeAskLo($request->input('ask_lo'));
                }
                if ($hasPublishedTripDetails) {
                    $publishedTripData['details'] = $request->details;
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
                    'date' => $flight->departure_date->format('Y-m-d'),
                    'departure_time' => $flight->departure_time,
                    'arrival_time' => $flight->arrival_time,
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
        $departureTime = $this->normalizeFlightTime($validated['departure_time'] ?? null, '08:00:00');
        $arrivalTime = $this->normalizeFlightTime($validated['arrival_time'] ?? null, '10:30:00');
        $hasUserTripRole = $this->hasColumn('user_trips', 'role');
        $hasUserTripNotes = $this->hasColumn('user_trips', 'notes');

        try {
            [$flight, $userTrip] = DB::transaction(function () use ($validated, $user, $departureDate, $departureTime, $arrivalTime, $hasUserTripRole, $hasUserTripNotes) {
                $flight = Flight::updateOrCreate(
                    [
                        'flight_number' => $validated['flight_number'],
                    ],
                    [
                        'departure_airport' => $validated['departure'],
                        'arrival_airport' => $validated['arrival'],
                        'departure_date' => $departureDate->toDateString(),
                        'departure_time' => $departureTime,
                        'arrival_time' => $arrivalTime,
                        'airline_id' => $user->airline_id,
                        'plane_type_id' => $user->plane_type_id,
                        'status' => 'scheduled',
                    ]
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
                    'date' => $flight->departure_date->format('Y-m-d'),
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
                
                return [
                    'id' => $swap->id,
                    'type' => $isRequester ? 'outgoing' : 'incoming',
                    'other_user' => $otherUser ? [
                        'id' => $otherUser->id,
                        'name' => $otherUser->full_name,
                    ] : null,
                    'flight' => $swap->publishedTrip && $swap->publishedTrip->flight ? [
                        'number' => $swap->publishedTrip->flight->flight_number,
                        'route' => $swap->publishedTrip->flight->departure_airport . ' → ' . 
                                  $swap->publishedTrip->flight->arrival_airport,
                        'date' => $swap->publishedTrip->flight->departure_date ? 
                                  $swap->publishedTrip->flight->departure_date->format('Y-m-d') : null,
                    ] : null,
                    'status' => $swap->status,
                    'message' => $swap->message,
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
