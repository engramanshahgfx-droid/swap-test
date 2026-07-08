<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PublishedTrip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FavoriteController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // ✅ FIX: Specify table name to avoid ambiguity
        $items = DB::table('user_favorite_trips')
            ->where('user_favorite_trips.user_id', $user->id)  // ← SPECIFY TABLE
            ->join('published_trips', 'published_trips.id', '=', 'user_favorite_trips.published_trip_id')
            ->select('published_trips.*')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $items
        ]);
    }

    public function store(Request $request, $tripId)
    {
        $user = $request->user();
        $trip = PublishedTrip::findOrFail($tripId);

        DB::table('user_favorite_trips')->updateOrInsert([
            'user_id' => $user->id,
            'published_trip_id' => $trip->id,
        ], [
            'updated_at' => now(),
            'created_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Favorited'
        ]);
    }

    public function destroy(Request $request, $tripId)
    {
        $user = $request->user();

        DB::table('user_favorite_trips')
            ->where('user_id', $user->id)
            ->where('published_trip_id', $tripId)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Unfavorited'
        ]);
    }
}
