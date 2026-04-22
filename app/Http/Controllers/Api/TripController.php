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
                'offer_lo' => $trip->offer_lo,
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

        try {
            [$flight, $publishedTrip] = DB::transaction(function () use ($request, $user, $departureDate) {
                // flights.flight_number is unique, so update or create using that key.
                $flight = Flight::updateOrCreate(
                    [
                        'flight_number' => $request->flight_number,
                    ],
                    [
                        'departure_airport' => $request->departure,
                        'arrival_airport' => $request->arrival,
                        'departure_date' => $departureDate->toDateString(),
                        'departure_time' => '08:00:00',
                        'arrival_time' => '10:30:00',
                        'airline_id' => $user->airline_id,
                        'plane_type_id' => $user->plane_type_id,
                        'status' => 'scheduled',
                    ]
                );

                // Ensure assignment exists for this user and flight.
                $userTripDefaults = [
                    'status' => 'assigned',
                ];

                if ($this->hasColumn('user_trips', 'role')) {
                    $userTripDefaults['role'] = $request->position;
                }

                if ($this->hasColumn('user_trips', 'notes')) {
                    $userTripDefaults['notes'] = $request->notes;
                }

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

                if ($this->hasColumn('published_trips', 'user_id')) {
                    $publishedTripData['user_id'] = $user->id;
                }
                if ($this->hasColumn('published_trips', 'flight_id')) {
                    $publishedTripData['flight_id'] = $flight->id;
                }
                if ($this->hasColumn('published_trips', 'user_trip_id')) {
                    $publishedTripData['user_trip_id'] = $userTrip->id;
                }
                if ($this->hasColumn('published_trips', 'flight_number')) {
                    $publishedTripData['flight_number'] = $request->flight_number;
                }
                if ($this->hasColumn('published_trips', 'legs')) {
                    $publishedTripData['legs'] = $request->legs;
                }
                if ($this->hasColumn('published_trips', 'fly_type')) {
                    $publishedTripData['fly_type'] = $request->fly_type;
                }
                if ($this->hasColumn('published_trips', 'report_time')) {
                    $publishedTripData['report_time'] = $request->report_time;
                }
                if ($this->hasColumn('published_trips', 'offer_lo')) {
                    $publishedTripData['offer_lo'] = $request->offer_lo;
                }
                if ($this->hasColumn('published_trips', 'ask_lo')) {
                    $publishedTripData['ask_lo'] = $this->serializeAskLo($request->input('ask_lo'));
                }
                if ($this->hasColumn('published_trips', 'details')) {
                    $publishedTripData['details'] = $request->details;
                }
                if ($this->hasColumn('published_trips', 'notes')) {
                    $publishedTripData['notes'] = $request->notes;
                }

                $publishedTrip = PublishedTrip::create($publishedTripData);

                return [$flight, $publishedTrip];
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
                'position' => $request->position,
                'status' => 'available',
                'expires_at' => $publishedTrip->expires_at,
                'flight_number' => $publishedTrip->flight_number,
                'legs' => $publishedTrip->legs,
                'fly_type' => $publishedTrip->fly_type,
                'report_time' => $publishedTrip->report_time,
                'offer_lo' => $publishedTrip->offer_lo,
                'ask_lo' => $this->parseAskLo($publishedTrip->ask_lo),
                'details' => $publishedTrip->details,
                'notes' => $publishedTrip->notes,
            ],
        ], 201);
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
