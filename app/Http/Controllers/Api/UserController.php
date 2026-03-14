<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
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

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $users->items(),
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
        return response()->json($request->user());
    }

    public function showById(User $user)
    {
        return response()->json([
            'success' => true,
            'data' => $user->load(['airline', 'position', 'planeType']),
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
            'user' => $request->user(),
        ]);
    }
}
