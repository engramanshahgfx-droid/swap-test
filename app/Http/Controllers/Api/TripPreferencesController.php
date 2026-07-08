<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateTripPreferencesRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TripPreferencesController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        $pref = DB::table('trip_preferences')->where('user_id', $user->id)->first();

        return response()->json(['success' => true, 'data' => $pref]);
    }

    public function update(UpdateTripPreferencesRequest $request)
    {
        $user = $request->user();

        $data = [
            'prefer_destinations' => $request->input('prefer_destinations') ? json_encode($request->input('prefer_destinations')) : null,
            'exclude_destinations' => $request->input('exclude_destinations') ? json_encode($request->input('exclude_destinations')) : null,
            'prefer_flight_types' => $request->input('prefer_flight_types') ? json_encode($request->input('prefer_flight_types')) : null,
            'updated_at' => now(),
        ];

        DB::table('trip_preferences')->updateOrInsert(['user_id' => $user->id], $data + ['user_id' => $user->id, 'created_at' => now()]);

        return response()->json(['success' => true, 'message' => 'Trip preferences updated.']);
    }
}
