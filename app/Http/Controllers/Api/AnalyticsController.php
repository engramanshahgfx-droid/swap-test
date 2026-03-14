<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PublishedTrip;
use App\Models\Report;
use App\Models\SwapRequest;
use App\Models\User;

class AnalyticsController extends Controller
{
    public function index()
    {
        $days = collect(range(6, 0))->map(fn (int $offset) => now()->subDays($offset));

        $userGrowth = $days->map(fn ($day) => [
            'date' => $day->toDateString(),
            'value' => User::whereDate('created_at', $day)->count(),
        ])->values();

        $swapVolume = $days->map(fn ($day) => [
            'date' => $day->toDateString(),
            'value' => SwapRequest::whereDate('created_at', $day)->count(),
        ])->values();

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => [
                    'total_users' => User::count(),
                    'active_users' => User::where('status', 'active')->count(),
                    'total_swaps' => SwapRequest::count(),
                    'completed_swaps' => SwapRequest::where('status', 'completed')->count(),
                    'pending_swaps' => SwapRequest::whereIn('status', ['pending', 'approved_by_owner'])->count(),
                    'total_reports' => Report::count(),
                    'open_reports' => Report::where('status', 'pending')->count(),
                    'published_trips' => PublishedTrip::count(),
                ],
                'charts' => [
                    'user_growth_last_7_days' => $userGrowth,
                    'swap_volume_last_7_days' => $swapVolume,
                ],
            ],
        ]);
    }
}
