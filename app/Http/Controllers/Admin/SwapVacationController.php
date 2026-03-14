<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PublishedTrip;
use Illuminate\Http\Request;

class SwapVacationController extends Controller
{
    public function index(Request $request)
    {
        $query = PublishedTrip::with(['user', 'flight.airline', 'swapRequests'])->withCount('swapRequests');

        if ($search = $request->input('search')) {
            $query->where(function ($rootQuery) use ($search) {
                $rootQuery->whereHas('user', function ($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%")
                      ->orWhere('employee_id', 'like', "%{$search}%");
                })->orWhereHas('flight', function ($q) use ($search) {
                    $q->where('flight_number', 'like', "%{$search}%")
                      ->orWhere('departure_airport', 'like', "%{$search}%")
                      ->orWhere('arrival_airport', 'like', "%{$search}%");
                });
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $publishedTrips = $query->latest()->paginate(15)->withQueryString();

        return view('pages.swap-vacation', compact('publishedTrips'));
    }
}
