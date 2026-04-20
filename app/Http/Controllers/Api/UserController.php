<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Airport;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Cache resolved base values during a request to avoid repeated airport lookups.
     *
     * @var array<string, string|null>
     */
    private array $baseCodeCache = [];

    private function resolveBaseAirportCode(?string $base): ?string
    {
        if ($base === null) {
            return null;
        }

        $normalizedBase = trim($base);
        if ($normalizedBase === '') {
            return null;
        }

        if (array_key_exists($normalizedBase, $this->baseCodeCache)) {
            return $this->baseCodeCache[$normalizedBase];
        }

        if (strlen($normalizedBase) === 3) {
            $code = strtoupper($normalizedBase);
            $this->baseCodeCache[$normalizedBase] = $code;
            return $code;
        }

        $airport = Airport::query()
            ->where('is_active', true)
            ->where(function ($query) use ($normalizedBase) {
                $query->whereRaw('UPPER(iata_code) = ?', [strtoupper($normalizedBase)])
                    ->orWhereRaw('LOWER(name) = ?', [strtolower($normalizedBase)]);
            })
            ->first(['iata_code']);

        $code = $airport?->iata_code ? strtoupper($airport->iata_code) : null;
        $this->baseCodeCache[$normalizedBase] = $code;

        return $code;
    }

    private function enrichUserPayload(User $user): array
    {
        $user->loadMissing(['airline:id,name,code', 'position:id,name']);

        $payload = $user->toArray();

        $baseAirportCode = $this->resolveBaseAirportCode($user->country_base);

        $payload['company_id'] = $user->airline_id;
        $payload['company_name'] = $user->airline?->name;
        $payload['airline_id'] = $user->airline_id;
        $payload['airline_name'] = $user->airline?->name;
        $payload['airline_code'] = $user->airline?->code;
        $payload['position_id'] = $user->position_id;
        $payload['position_name'] = $user->position?->name;
        $payload['base_airport_code'] = $baseAirportCode;
        $payload['base_airport_name'] = $baseAirportCode;

        return $payload;
    }

    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => 'nullable|string|max:255',
            'status' => 'nullable|in:active,inactive,blocked',
            'airline_id' => 'nullable|integer|exists:airlines,id',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = User::query()
            ->with(['airline', 'position'])
            ->orderByDesc('created_at');

        if (!empty($validated['search'])) {
            $search = $validated['search'];
            $query->where(function ($searchQuery) use ($search) {
                $searchQuery->where('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('employee_id', 'like', "%{$search}%");
            });
        }

        if (!empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        if (!empty($validated['airline_id'])) {
            $query->where('airline_id', $validated['airline_id']);
        }

        $users = $query->paginate($validated['per_page'] ?? 20);

        $items = collect($users->items())
            ->map(fn (User $user) => $this->enrichUserPayload($user))
            ->values()
            ->all();

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $items,
                'pagination' => [
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                ],
            ],
        ]);
    }

    /**
     * Store Firebase device token for push notifications
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeDeviceToken(Request $request)
    {
        $validated = $request->validate([
            'device_token' => 'required|string',
        ]);

        $request->user()->update([
            'device_token' => $validated['device_token'],
        ]);

        return response()->json([
            'message' => 'Device token stored successfully',
            'device_token' => substr($validated['device_token'], 0, 20) . '...',
        ]);
    }

    /**
     * Get current user profile
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request)
    {
        return response()->json($this->enrichUserPayload($request->user()));
    }

    public function showById(User $user)
    {
        return response()->json([
            'success' => true,
            'data' => $this->enrichUserPayload($user),
        ]);
    }

    /**
     * Update user profile
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20',
            'country' => 'sometimes|string|max:100',
        ]);

        $request->user()->update($validated);

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $this->enrichUserPayload($request->user()),
        ]);
    }
}
