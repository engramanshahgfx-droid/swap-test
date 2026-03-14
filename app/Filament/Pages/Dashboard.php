<?php

namespace App\Filament\Pages;

use App\Models\Airline;
use App\Models\Airport;
use App\Models\Conversation;
use App\Models\Flight;
use App\Models\Message;
use App\Models\PlaneType;
use App\Models\Position;
use App\Models\PublishedTrip;
use App\Models\Report;
use App\Models\SwapRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static string $view = 'filament.pages.dashboard';

    protected function getViewData(): array
    {
        $dateRange = collect(range(6, 0))
            ->map(fn (int $daysAgo) => now()->subDays($daysAgo)->startOfDay());

        $resourceCards = [
            $this->buildResourceCard('Users', User::query(), User::where('status', 'active')->count() . ' active', '/admin/users', 'blue'),
            $this->buildResourceCard('Flights', Flight::query(), Flight::where('status', 'scheduled')->count() . ' scheduled', '/admin/flights', 'sky'),
            $this->buildResourceCard('Airlines', Airline::query(), Airline::where('is_active', true)->count() . ' active', '/admin/airlines', 'violet'),
            $this->buildResourceCard('Positions', Position::query(), 'Crew roles & hierarchy', '/admin/positions', 'amber'),
            $this->buildResourceCard('Airports', Airport::query(), Airport::where('is_active', true)->count() . ' active', '/admin/airports', 'emerald'),
            $this->buildResourceCard('Plane Types', PlaneType::query(), PlaneType::where('is_active', true)->count() . ' active', '/admin/plane-types', 'rose'),
            $this->buildResourceCard('Swaps', SwapRequest::query(), SwapRequest::where('status', 'pending')->count() . ' pending', '/admin/swap-requests', 'indigo'),
            $this->buildResourceCard('Reports', Report::query(), Report::where('status', 'pending')->count() . ' pending', '/admin/reports', 'red'),
        ];

        $growthLabels = $dateRange->map(fn ($date) => $date->format('M j'))->all();
        $growthSeries = [
            [
                'label' => 'Users',
                'color' => '#2563eb',
                'values' => $this->buildCreatedAtSeries(User::query(), $dateRange),
            ],
            [
                'label' => 'Flights',
                'color' => '#0f766e',
                'values' => $this->buildCreatedAtSeries(Flight::query(), $dateRange),
            ],
            [
                'label' => 'Swaps',
                'color' => '#7c3aed',
                'values' => $this->buildCreatedAtSeries(SwapRequest::query(), $dateRange),
            ],
            [
                'label' => 'Reports',
                'color' => '#dc2626',
                'values' => $this->buildCreatedAtSeries(Report::query(), $dateRange),
            ],
        ];

        $swapStatus = [
            ['label' => 'Pending', 'value' => SwapRequest::where('status', 'pending')->count(), 'color' => '#f59e0b'],
            ['label' => 'Owner Approved', 'value' => SwapRequest::where('status', 'approved_by_owner')->count(), 'color' => '#3b82f6'],
            ['label' => 'Completed', 'value' => SwapRequest::where('status', 'completed')->count(), 'color' => '#10b981'],
            ['label' => 'Rejected', 'value' => SwapRequest::whereIn('status', ['rejected', 'rejected_by_owner', 'manager_rejected'])->count(), 'color' => '#ef4444'],
        ];

        $reportStatus = [
            ['label' => 'Pending', 'value' => Report::where('status', 'pending')->count(), 'color' => '#f97316'],
            ['label' => 'Reviewed', 'value' => Report::where('status', 'reviewed')->count(), 'color' => '#0ea5e9'],
            ['label' => 'Resolved', 'value' => Report::where('status', 'resolved')->count(), 'color' => '#16a34a'],
        ];

        $airlineActivity = Airline::query()
            ->withCount(['users', 'flights'])
            ->get()
            ->sortByDesc(fn (Airline $airline) => $airline->users_count + $airline->flights_count)
            ->take(6)
            ->values()
            ->map(fn (Airline $airline) => [
                'label' => $airline->code ?: $airline->name,
                'value' => $airline->users_count + $airline->flights_count,
            ])
            ->all();

        $recentUsers = User::query()
            ->with(['airline', 'position'])
            ->latest()
            ->take(6)
            ->get()
            ->map(fn (User $user) => [
                'title' => $user->full_name,
                'subtitle' => trim(($user->employee_id ? $user->employee_id . ' · ' : '') . ($user->position?->name ?? 'No position')),
                'meta' => $user->airline?->name ?? 'No airline',
                'status' => ucfirst($user->status ?? 'unknown'),
                'timestamp' => optional($user->created_at)->diffForHumans(),
            ])
            ->all();

        $recentSwaps = SwapRequest::query()
            ->with(['requester', 'responder', 'publishedTrip.flight'])
            ->latest()
            ->take(6)
            ->get()
            ->map(fn (SwapRequest $swap) => [
                'title' => ($swap->requester?->full_name ?? 'Unknown requester') . ' -> ' . ($swap->responder?->full_name ?? 'Unknown responder'),
                'subtitle' => $swap->publishedTrip?->flight?->flight_number
                    ? $swap->publishedTrip->flight->flight_number . ' · ' . $swap->publishedTrip->flight->departure_airport . ' to ' . $swap->publishedTrip->flight->arrival_airport
                    : 'Flight details unavailable',
                'meta' => ucfirst(str_replace('_', ' ', $swap->status)),
                'status' => ucfirst(str_replace('_', ' ', $swap->manager_approval_status ?? 'pending')),
                'timestamp' => optional($swap->created_at)->diffForHumans(),
            ])
            ->all();

        $recentReports = Report::query()
            ->with(['reporter', 'reportedUser', 'reviewedBy'])
            ->latest()
            ->take(6)
            ->get()
            ->map(fn (Report $report) => [
                'title' => ($report->reporter?->full_name ?? 'Unknown reporter') . ' reported ' . ($report->reportedUser?->full_name ?? 'Unknown user'),
                'subtitle' => ucfirst(str_replace('_', ' ', $report->reason)) . ' · ' . str($report->details)->limit(48),
                'meta' => $report->reviewedBy?->full_name ? 'Reviewed by ' . $report->reviewedBy->full_name : 'Awaiting moderator',
                'status' => ucfirst($report->status),
                'timestamp' => optional($report->created_at)->diffForHumans(),
            ])
            ->all();

        $serviceStats = [
            ['label' => 'Published trips', 'value' => PublishedTrip::count()],
            ['label' => 'Conversations', 'value' => Conversation::count()],
            ['label' => 'Messages', 'value' => Message::count()],
            ['label' => 'Active airlines', 'value' => Airline::where('is_active', true)->count()],
        ];

        $dashboardData = [
            'growth' => [
                'labels' => $growthLabels,
                'series' => $growthSeries,
            ],
            'swapStatus' => $swapStatus,
            'reportStatus' => $reportStatus,
            'airlineActivity' => $airlineActivity,
        ];

        return [
            'resourceCards' => $resourceCards,
            'recentUsers' => $recentUsers,
            'recentSwaps' => $recentSwaps,
            'recentReports' => $recentReports,
            'serviceStats' => $serviceStats,
            'dashboardData' => $dashboardData,
            'lastUpdated' => now()->format('M j, Y g:i A'),
        ];
    }

    private function buildResourceCard(string $label, Builder $query, string $meta, string $url, string $tone): array
    {
        $count = (clone $query)->count();
        $currentWindowStart = now()->subDays(6)->startOfDay();
        $previousWindowStart = now()->subDays(13)->startOfDay();
        $previousWindowEnd = now()->subDays(7)->endOfDay();

        $current = (clone $query)->where('created_at', '>=', $currentWindowStart)->count();
        $previous = (clone $query)
            ->whereBetween('created_at', [$previousWindowStart, $previousWindowEnd])
            ->count();

        $delta = $current - $previous;
        $trend = $delta > 0 ? 'up' : ($delta < 0 ? 'down' : 'flat');

        return [
            'label' => $label,
            'value' => $count,
            'meta' => $meta,
            'tone' => $tone,
            'url' => url($url),
            'deltaLabel' => ($delta > 0 ? '+' : '') . $delta,
            'deltaText' => $current . ' this week',
            'trend' => $trend,
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

