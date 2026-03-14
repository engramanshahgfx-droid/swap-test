<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Airline;
use App\Models\Flight;
use App\Models\PublishedTrip;
use App\Models\SwapRequest;
use App\Models\User;

class AnalyticsController extends Controller
{
    public function index()
    {
        $days = collect(range(6, 0))->map(fn (int $offset) => now()->subDays($offset));

        $userGrowth = $days->map(fn ($day) => [
            'label' => $day->format('D'),
            'value' => User::whereDate('created_at', $day)->count(),
        ]);

        $swapVolume = $days->map(fn ($day) => [
            'label' => $day->format('D'),
            'value' => SwapRequest::whereDate('created_at', $day)->count(),
        ]);

        $airlineActivity = Airline::withCount(['users', 'flights'])
            ->get()
            ->map(fn (Airline $airline) => [
                'name' => $airline->name,
                'code' => $airline->code,
                'score' => $airline->users_count + $airline->flights_count,
            ])
            ->sortByDesc('score')
            ->take(6)
            ->values();

        $topAirports = Flight::selectRaw('departure_airport as airport, COUNT(*) as total')
            ->whereNotNull('departure_airport')
            ->groupBy('departure_airport')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        $topCrewMembers = User::withCount([
            'publishedTrips',
            'swapRequestsAsRequester',
            'swapRequestsAsResponder',
        ])
            ->get()
            ->map(function (User $user) {
                $score = $user->published_trips_count + $user->swap_requests_as_requester_count + $user->swap_requests_as_responder_count;

                return [
                    'name' => $user->full_name,
                    'employee_id' => $user->employee_id,
                    'airline' => $user->airline?->name,
                    'score' => $score,
                ];
            })
            ->sortByDesc('score')
            ->take(8)
            ->values();

        $stats = [
            'total_users' => User::count(),
            'total_swaps' => SwapRequest::count(),
            'published_trips' => PublishedTrip::count(),
            'total_flights' => Flight::count(),
        ];

        return view('pages.analytics', compact('userGrowth', 'swapVolume', 'airlineActivity', 'topAirports', 'topCrewMembers', 'stats'));
    }
}
