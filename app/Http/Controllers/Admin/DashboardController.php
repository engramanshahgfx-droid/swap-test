<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Airline;
use App\Models\Flight;
use App\Models\Message;
use App\Models\PublishedTrip;
use App\Models\Report;
use App\Models\SwapRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $dateRange = collect(range(6, 0))->map(fn (int $daysAgo) => now()->subDays($daysAgo)->startOfDay());

        $stats = [
            'total_flights' => Flight::count(),
            'total_users'    => User::count(),
            'total_airlines' => Airline::count(),
            'active_users'   => User::where('status', 'active')->count(),
            'total_swap_posts' => PublishedTrip::count(),
            'completed_swaps' => SwapRequest::where('status', 'completed')->count(),
            'pending_swaps'  => SwapRequest::whereIn('status', ['pending', 'approved_by_owner'])->count(),
            'total_reports'  => Report::count(),
            'open_reports'   => Report::where('status', 'pending')->count(),
        ];

        $trends = [
            'flights' => $this->compareCurrentToPreviousWeek(Flight::query()),
            'airlines' => $this->compareCurrentToPreviousWeek(Airline::query()),
            'users' => $this->compareCurrentToPreviousWeek(User::query()),
            'reports' => $this->compareCurrentToPreviousWeek(Report::query()),
            'swaps' => $this->compareCurrentToPreviousWeek(PublishedTrip::query()),
        ];

        $dashboardData = [
            'labels' => $dateRange->map(fn ($date) => $date->format('M j'))->all(),
            'swapActivity' => [
                'labels' => $dateRange->map(fn ($date) => $date->format('D'))->all(),
                'series' => [
                    [
                        'label' => 'Offer posts',
                        'color' => '#2563eb',
                        'values' => $this->buildCreatedAtSeries(PublishedTrip::query(), $dateRange),
                    ],
                    [
                        'label' => 'Ask requests',
                        'color' => '#f97316',
                        'values' => $this->buildCreatedAtSeries(SwapRequest::query(), $dateRange),
                    ],
                ],
            ],
            'userGrowth' => [
                'labels' => $dateRange->map(fn ($date) => $date->format('M j'))->all(),
                'series' => [
                    [
                        'label' => 'New users',
                        'color' => '#0f766e',
                        'values' => $this->buildCreatedAtSeries(User::query(), $dateRange),
                    ],
                ],
            ],
            'swapStatus' => [
                ['label' => 'Available', 'value' => PublishedTrip::whereIn('status', ['available', 'active'])->count(), 'color' => '#2563eb'],
                ['label' => 'Completed', 'value' => SwapRequest::where('status', 'completed')->count(), 'color' => '#10b981'],
                ['label' => 'Cancelled', 'value' => SwapRequest::whereIn('status', ['rejected', 'rejected_by_owner', 'manager_rejected'])->count(), 'color' => '#ef4444'],
            ],
            'reportStatus' => [
                ['label' => 'Waiting', 'value' => Report::where('status', 'pending')->count(), 'color' => '#f97316'],
                ['label' => 'Resolved', 'value' => Report::where('status', 'resolved')->count(), 'color' => '#16a34a'],
                ['label' => 'Rejected', 'value' => Report::where('status', 'reviewed')->count(), 'color' => '#8b5cf6'],
            ],
            'airlineActivity' => Airline::query()
                ->withCount(['users', 'flights'])
                ->get()
                ->sortByDesc(fn (Airline $airline) => $airline->users_count + $airline->flights_count)
                ->take(6)
                ->values()
                ->map(fn (Airline $airline) => [
                    'label' => $airline->code ?: $airline->name,
                    'value' => $airline->users_count + $airline->flights_count,
                    'color' => '#4f86f7',
                ])
                ->all(),
            'weeklyHeatmap' => $dateRange
                ->map(function ($date) {
                    $day = $date->toDateString();

                    $count = User::whereDate('created_at', $day)->count()
                        + PublishedTrip::whereDate('created_at', $day)->count()
                        + SwapRequest::whereDate('created_at', $day)->count()
                        + Report::whereDate('created_at', $day)->count()
                        + Message::whereDate('created_at', $day)->count();

                    return [
                        'label' => $date->format('D'),
                        'fullLabel' => $date->format('M j'),
                        'value' => $count,
                    ];
                })
                ->all(),
        ];

        $recent_swaps = SwapRequest::with(['requester', 'responder', 'publishedTrip.flight'])
            ->latest()
            ->take(5)
            ->get();

        $recent_reports = Report::with(['reporter', 'reportedUser'])
            ->latest()
            ->take(5)
            ->get();

        $tripWidgetStats = [
            'my_published_trips' => PublishedTrip::where('user_id', auth()->id())->count(),
            'total_published_trips' => PublishedTrip::count(),
            'assigned_trips' => SwapRequest::whereIn('status', ['approved', 'approved_by_owner', 'manager_approved', 'completed'])
                ->distinct('published_trip_id')
                ->count('published_trip_id'),
            'pending_assignments' => SwapRequest::where('status', 'pending')->count(),
        ];

        $recent_trips = PublishedTrip::with(['user', 'flight.departureAirport', 'flight.arrivalAirport'])
            ->withCount([
                'swapRequests as assigned_requests_count' => function ($query) {
                    $query->whereIn('status', ['approved', 'approved_by_owner', 'manager_approved', 'completed']);
                },
                'swapRequests as pending_requests_count' => function ($query) {
                    $query->where('status', 'pending');
                },
            ])
            ->latest()
            ->take(6)
            ->get();

        // Fetch all users with relationships for admin dashboard
        $users = User::with(['airline', 'planeType', 'position'])
            ->paginate(15);

        return view('pages.dashboard', compact('stats', 'trends', 'dashboardData', 'recent_swaps', 'recent_reports', 'tripWidgetStats', 'recent_trips', 'users'));
    }

    private function compareCurrentToPreviousWeek(Builder $query): array
    {
        $currentStart = now()->subDays(6)->startOfDay();
        $previousStart = now()->subDays(13)->startOfDay();
        $previousEnd = now()->subDays(7)->endOfDay();

        $current = (clone $query)->where('created_at', '>=', $currentStart)->count();
        $previous = (clone $query)->whereBetween('created_at', [$previousStart, $previousEnd])->count();
        $delta = $current - $previous;

        return [
            'delta' => ($delta > 0 ? '+' : '') . $delta,
            'class' => $delta >= 0 ? 'up' : 'down',
            'text' => $current . ' this week',
        ];
    }

    private function buildCreatedAtSeries(Builder $query, Collection $dateRange): array
    {
        $counts = (clone $query)
            ->selectRaw('DATE(created_at) as day_key, COUNT(*) as aggregate')
            ->where('created_at', '>=', $dateRange->first())
            ->groupBy(DB::raw('DATE(created_at)'))
            ->pluck('aggregate', 'day_key');

        return $dateRange
            ->map(fn ($date) => (int) ($counts[$date->toDateString()] ?? 0))
            ->all();
    }
}
