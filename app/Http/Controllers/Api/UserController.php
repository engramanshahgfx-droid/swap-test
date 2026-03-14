<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
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
