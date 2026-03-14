<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SwapRequest;
use Illuminate\Http\Request;

class SwapFlightController extends Controller
{
    public function index(Request $request)
    {
        $query = SwapRequest::with([
            'requester',
            'responder',
            'publishedTrip.flight',
            'requesterTrip.flight',
        ]);

        if ($search = $request->input('search')) {
            $query->where(function ($rootQuery) use ($search) {
                $rootQuery->whereHas('requester', function ($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%")
                      ->orWhere('employee_id', 'like', "%{$search}%");
                })->orWhereHas('responder', function ($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%")
                      ->orWhere('employee_id', 'like', "%{$search}%");
                })->orWhereHas('publishedTrip.flight', function ($q) use ($search) {
                    $q->where('flight_number', 'like', "%{$search}%")
                      ->orWhere('departure_airport', 'like', "%{$search}%")
                      ->orWhere('arrival_airport', 'like', "%{$search}%");
                });
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $swaps = $query->latest()->paginate(15)->withQueryString();

        return view('pages.swap-flight', compact('swaps'));
    }

    public function updateStatus(Request $request, SwapRequest $swap)
    {
        $request->validate([
            'status' => 'required|in:pending,approved_by_owner,rejected_by_owner,manager_rejected,completed',
        ]);

        $swap->update(['status' => $request->status]);

        return redirect()->route('swap-flight')->with('success', 'Swap status updated.');
    }
}
