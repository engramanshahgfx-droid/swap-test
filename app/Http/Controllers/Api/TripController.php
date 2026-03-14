<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PublishTripRequest;
use App\Models\PublishedTrip;
use App\Models\UserTrip;
use App\Models\SwapRequest;
use App\Services\SwapService;
use Illuminate\Http\Request;

class TripController extends Controller
{
    protected $swapService;

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

        $items = $eligibleTrips->forPage($page, $perPage)->values();

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
        
        // Parse the date from request
        $departureDate = \Carbon\Carbon::parse($request->date);
        
        // Find or create flight - use only identifying columns in WHERE, rest in attributes
        $flight = \App\Models\Flight::firstOrCreate(
            [
                'flight_number' => $request->flight_number,
                'departure_airport' => $request->departure,
                'arrival_airport' => $request->arrival,
            ],
            [
                'departure_date' => $departureDate,
                'departure_time' => '08:00:00',
                'arrival_time' => '10:30:00',
                'airline_id' => $user->airline_id,
                'plane_type_id' => $user->plane_type_id,
                'status' => 'scheduled',
            ]
        );
        
        // Create or get UserTrip
        $userTrip = \App\Models\UserTrip::firstOrCreate(
            [
                'user_id' => $user->id,
                'flight_id' => $flight->id,
            ],
            [
                'status' => 'assigned',
                'role' => $request->position,
                'notes' => $request->notes,
            ]
        );
        
        // Publish the trip
        $publishedTrip = PublishedTrip::create([
            'user_id' => $user->id,
            'flight_id' => $flight->id,
            'notes' => $request->notes,
            'status' => 'active',
            'published_at' => now(),
            'expires_at' => $request->expires_at ?? now()->addDays(7),
        ]);

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
