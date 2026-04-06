<?php

namespace App\Http\Controllers\Api;

use App\Models\PublishedTrip;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Schema;

class VacationController extends Controller
{
    /**
     * Publish a vacation (month-based)
     * POST /api/publish-vacation
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function publishVacation(Request $request)
    {
        // Validate request
        $validated = $request->validate([
            'publisher_id' => 'required|exists:users,id',
            'departure_month' => 'required|string|size:3', // e.g., "APR", "MAY"
            'arrival_month' => 'required|string|size:3',   // e.g., "APR", "MAY"
            'notes' => 'nullable|string|max:500',
            'position' => 'nullable|string|in:Captain,First Officer,Purser,Flight Attendant',
        ]);

        // Get or verify publisher
        $publisher = User::findOrFail($validated['publisher_id']);

        // Convert month abbreviations to dates (assume current year)
        $departureMonth = $this->monthToDate($validated['departure_month']);
        $arrivalMonth = $this->monthToDate($validated['arrival_month']);

        // Ensure dates are valid (arrival month >= departure month)
        if ($arrivalMonth < $departureMonth) {
            // Assume next year if arrival month is before departure month
            $arrivalMonth->addYear();
        }

        try {
            $hasVacationTypeColumn = $this->publishedTripsHasColumn('vacation_type');
            $hasMetadataColumn = $this->publishedTripsHasColumn('metadata');

            $metadata = [
                'departure_month' => $validated['departure_month'],
                'arrival_month' => $validated['arrival_month'],
                'position' => $validated['position'] ?? 'Any',
            ];

            $notes = $validated['notes'] ?? "Vacation from {$validated['departure_month']} to {$validated['arrival_month']}";
            if (!$hasMetadataColumn) {
                $notes .= " | Position: " . ($validated['position'] ?? 'Any');
            }

            $createPayload = [
                'user_id' => $publisher->id,
                'flight_id' => null,
                'status' => 'available',
                'notes' => $notes,
                'published_at' => now(),
                'expires_at' => $arrivalMonth->clone()->endOfMonth(),
            ];

            if ($hasVacationTypeColumn) {
                $createPayload['vacation_type'] = 'month_range';
            }

            if ($hasMetadataColumn) {
                $createPayload['metadata'] = $metadata;
            }

            // Create published vacation
            $publishedVacation = PublishedTrip::create($createPayload);

            return response()->json([
                'success' => true,
                'message' => 'Vacation published successfully',
                'data' => [
                    'id' => $publishedVacation->id,
                    'publisher_id' => $publisher->id,
                    'publisher_name' => $publisher->full_name,
                    'departure_month' => $validated['departure_month'],
                    'arrival_month' => $validated['arrival_month'],
                    'departure_date' => $departureMonth->format('Y-m-d'),
                    'arrival_date' => $arrivalMonth->endOfMonth()->format('Y-m-d'),
                    'position' => $validated['position'] ?? 'Any',
                    'status' => 'available',
                    'published_at' => $publishedVacation->published_at,
                    'expires_at' => $publishedVacation->expires_at,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to publish vacation',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user's vacation publishes
     * GET /api/my-vacations
     */
    public function myVacations(Request $request)
    {
        $user = $request->user();
        $perPage = max(1, min((int) $request->integer('per_page', 20), 100));

        $vacations = PublishedTrip::where('user_id', $user->id)
            ->when(
                $this->publishedTripsHasColumn('vacation_type'),
                fn ($query) => $query->where('vacation_type', 'month_range'),
                fn ($query) => $query
            )
            ->orderBy('published_at', 'desc')
            ->paginate($perPage)
            ->through(function ($vacation) {
                $months = $this->extractMonthsFromVacation($vacation);

                return [
                    'id' => $vacation->id,
                    'departure_month' => $months['departure_month'],
                    'arrival_month' => $months['arrival_month'],
                    'position' => $vacation->metadata['position'] ?? 'Any',
                    'status' => $vacation->status,
                    'published_at' => $vacation->published_at,
                    'expires_at' => $vacation->expires_at,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $vacations->items(),
                'pagination' => [
                    'current_page' => $vacations->currentPage(),
                    'last_page' => $vacations->lastPage(),
                    'per_page' => $vacations->perPage(),
                    'total' => $vacations->total(),
                ],
            ],
        ]);
    }

    /**
     * Browse vacation publishes (from other users)
     * GET /api/browse-vacations
     */
    public function browseVacations(Request $request)
    {
        $user = $request->user();
        $perPage = max(1, min((int) $request->integer('per_page', 20), 100));
        $page = max(1, (int) $request->integer('page', 1));

        $vacations = PublishedTrip::where('user_id', '!=', $user->id)
            ->when(
                $this->publishedTripsHasColumn('vacation_type'),
                fn ($query) => $query->where('vacation_type', 'month_range'),
                fn ($query) => $query
            )
            ->where('status', 'available')
            ->where('expires_at', '>', now())
            ->with('user')
            ->orderBy('published_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page)
            ->through(function ($vacation) {
                $months = $this->extractMonthsFromVacation($vacation);

                return [
                    'id' => $vacation->id,
                    'publisher' => [
                        'id' => $vacation->user->id,
                        'name' => $vacation->user->full_name,
                        'employee_id' => $vacation->user->employee_id,
                    ],
                    'departure_month' => $months['departure_month'],
                    'arrival_month' => $months['arrival_month'],
                    'position' => $vacation->metadata['position'] ?? 'Any',
                    'notes' => $vacation->notes,
                    'published_at' => $vacation->published_at,
                    'expires_at' => $vacation->expires_at,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $vacations->items(),
                'pagination' => [
                    'current_page' => $vacations->currentPage(),
                    'last_page' => $vacations->lastPage(),
                    'per_page' => $vacations->perPage(),
                    'total' => $vacations->total(),
                ],
            ],
        ]);
    }

    /**
     * Helper: Convert month abbreviation to date
     */
    private function monthToDate($monthAbbr)
    {
        $now = now();
        $monthMap = [
            'JAN' => 1, 'FEB' => 2, 'MAR' => 3, 'APR' => 4,
            'MAY' => 5, 'JUN' => 6, 'JUL' => 7, 'AUG' => 8,
            'SEP' => 9, 'OCT' => 10, 'NOV' => 11, 'DEC' => 12,
        ];

        $month = $monthMap[strtoupper($monthAbbr)] ?? null;
        if (!$month) {
            throw new \InvalidArgumentException("Invalid month: $monthAbbr");
        }

        // If month is in the past, assume next year
        $date = $now->clone()->month($month)->startOfMonth();
        if ($date < $now->startOfMonth()) {
            $date->addYear();
        }

        return $date;
    }

    /**
     * Check if a column exists on published_trips with per-request static cache.
     */
    private function publishedTripsHasColumn(string $column): bool
    {
        static $cache = [];

        if (!array_key_exists($column, $cache)) {
            $cache[$column] = Schema::hasColumn('published_trips', $column);
        }

        return $cache[$column];
    }

    /**
     * Read months from metadata when available, otherwise parse legacy notes.
     */
    private function extractMonthsFromVacation(PublishedTrip $vacation): array
    {
        $departure = $vacation->metadata['departure_month'] ?? null;
        $arrival = $vacation->metadata['arrival_month'] ?? null;

        if ($departure && $arrival) {
            return [
                'departure_month' => $departure,
                'arrival_month' => $arrival,
            ];
        }

        if (is_string($vacation->notes) && preg_match('/Vacation from\s+([A-Z]{3})\s+to\s+([A-Z]{3})/i', $vacation->notes, $matches)) {
            return [
                'departure_month' => strtoupper($matches[1]),
                'arrival_month' => strtoupper($matches[2]),
            ];
        }

        return [
            'departure_month' => null,
            'arrival_month' => null,
        ];
    }
}
