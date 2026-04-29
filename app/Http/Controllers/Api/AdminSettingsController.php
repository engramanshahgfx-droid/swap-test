<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AdminSettingsService;
use Illuminate\Http\Request;

class AdminSettingsController extends Controller
{
    public function __construct(protected AdminSettingsService $settingsService)
    {
    }

    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => $this->settingsService->all(),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'request_cutoff_hours' => 'required|integer|min:0|max:168',
            'default_departure_time' => 'nullable|date_format:H:i',
            'default_arrival_time' => 'nullable|date_format:H:i',
        ]);

        $saved = $this->settingsService->update([
            'request_cutoff_hours' => $validated['request_cutoff_hours'],
            'default_departure_time' => $validated['default_departure_time'] ?? config('swap.default_departure_time'),
            'default_arrival_time' => $validated['default_arrival_time'] ?? config('swap.default_arrival_time'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Admin settings updated successfully.',
            'data' => $saved,
        ]);
    }
}
