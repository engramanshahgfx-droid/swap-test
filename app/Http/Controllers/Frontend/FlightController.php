<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Flight;
use App\Models\Airport;
use App\Models\Airline;
use App\Models\PlaneType;
use App\Models\UserTrip;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FlightController extends Controller
{
    // Show flights dashboard
    public function index()
    {
        try {
            $flights = Flight::with('airline', 'planeType', 'departureAirport', 'arrivalAirport')->get();
            $userFlights = Auth::check() ? Auth::user()->userTrips()->with('flight')->get() : collect([]);
        } catch (\Exception $e) {
            // If database fails, use empty collections
            $flights = collect([]);
            $userFlights = collect([]);
        }

        return view('frontend.flights.index', compact('flights', 'userFlights'));
    }

    // Show add flight form
    public function showAddFlight()
    {
        try {
            $airports = Airport::all();
            $airlines = Airline::all();
            $planeTypes = PlaneType::all();
        } catch (\Exception $e) {
            // If database is offline, provide dummy data
            $airports = collect([
                (object)['id' => 1, 'name' => 'Dubai International', 'code' => 'DXB'],
                (object)['id' => 2, 'name' => 'Doha Hamad', 'code' => 'DOH'],
            ]);
            $airlines = collect([
                (object)['id' => 1, 'name' => 'Emirates Airlines', 'code' => 'EK'],
                (object)['id' => 2, 'name' => 'Qatar Airways', 'code' => 'QR'],
            ]);
            $planeTypes = collect([
                (object)['id' => 1, 'name' => 'Boeing 747', 'code' => 'B747'],
                (object)['id' => 2, 'name' => 'Airbus A380', 'code' => 'A380'],
            ]);
        }

        return view('frontend.flights.add', compact('airports', 'airlines', 'planeTypes'));
    }

    // Store new flight
    public function addFlight(Request $request)
    {
        $validated = $request->validate([
            'flight_number' => 'required',
            'airline_id' => 'required',
            'plane_type_id' => 'required',
            'departure_airport_id' => 'required',
            'arrival_airport_id' => 'required',
            'departure_date' => 'required|date',
            'departure_time' => 'required|date_format:H:i',
            'arrival_time' => 'required|date_format:H:i',
            'status' => 'required|in:scheduled,completed,cancelled',
        ]);

        try {
            // Combine date and time
            $departureDateTime = $validated['departure_date'] . ' ' . $validated['departure_time'];
            $arrivalDateTime = $validated['departure_date'] . ' ' . $validated['arrival_time'];

            $flight = Flight::create([
                'flight_number' => $validated['flight_number'],
                'airline_id' => $validated['airline_id'],
                'plane_type_id' => $validated['plane_type_id'],
                'departure_airport_id' => $validated['departure_airport_id'],
                'arrival_airport_id' => $validated['arrival_airport_id'],
                'departure_date' => $validated['departure_date'],
                'departure_time' => $departureDateTime,
                'arrival_time' => $arrivalDateTime,
                'status' => $validated['status'],
            ]);

            return redirect()->route('frontend.flights.index')->with('success', 'Flight added successfully!');
        } catch (\Exception $e) {
            // If database is offline, store in session
            session([
                'added_flight' => [
                    'flight_number' => $validated['flight_number'],
                    'airline_id' => $validated['airline_id'],
                    'plane_type_id' => $validated['plane_type_id'],
                    'departure_airport_id' => $validated['departure_airport_id'],
                    'arrival_airport_id' => $validated['arrival_airport_id'],
                    'departure_date' => $validated['departure_date'],
                    'departure_time' => $validated['departure_time'],
                    'arrival_time' => $validated['arrival_time'],
                    'status' => $validated['status'],
                ]
            ]);
            
            return redirect()->route('frontend.flights.index')->with('success', 'Flight added successfully! (Saved locally)');
        }
    }

    // Show flight details
    public function show(Flight $flight)
    {
        $assignedCrew = $flight->assignedCrew()->with('user')->get();

        return view('frontend.flights.show', compact('flight', 'assignedCrew'));
    }

    // Join flight (assign user to flight)
    public function joinFlight(Flight $flight)
    {
        $userId = $this->resolveCurrentUserId();

        if (!$userId) {
            return back()->with('error', 'Unable to identify your account. Please login again with a database-backed user.');
        }

        $exists = UserTrip::where('user_id', $userId)
            ->where('flight_id', $flight->id)
            ->exists();

        if ($exists) {
            return back()->with('error', 'You are already assigned to this flight');
        }

        UserTrip::create([
            'user_id' => $userId,
            'flight_id' => $flight->id,
            'status' => 'assigned',
            'role' => $this->resolveCurrentUserRole(),
        ]);

        return back()->with('success', 'Successfully joined the flight!');
    }

    // Leave flight (remove user from flight)
    public function leaveFlight(Flight $flight)
    {
        $userId = $this->resolveCurrentUserId();

        if (!$userId) {
            return back()->with('error', 'Unable to identify your account. Please login again with a database-backed user.');
        }

        UserTrip::where('user_id', $userId)
            ->where('flight_id', $flight->id)
            ->delete();

        return back()->with('success', 'Successfully left the flight!');
    }

    // Show my flights
    public function myFlights()
    {
        $userId = $this->resolveCurrentUserId();

        if (!$userId) {
            return redirect()->route('frontend.flights.index')->with('error', 'No database user found for this session account.');
        }

        $userFlights = UserTrip::where('user_id', $userId)
            ->with('flight.airline', 'flight.planeType', 'flight.departureAirport', 'flight.arrivalAirport')
            ->get();

        return view('frontend.flights.my-flights', compact('userFlights'));
    }

    private function resolveCurrentUserId(): ?int
    {
        if (Auth::id()) {
            return (int) Auth::id();
        }

        $sessionEmail = session('user_data.email');

        if (!$sessionEmail) {
            return null;
        }

        $userId = User::where('email', $sessionEmail)->value('id');

        return $userId ? (int) $userId : null;
    }

    private function resolveCurrentUserRole(): string
    {
        if (Auth::check()) {
            return Auth::user()->position->name ?? 'crew';
        }

        $sessionPositionId = session('user_data.position_id');

        if (!$sessionPositionId) {
            return 'crew';
        }

        $positionName = \App\Models\Position::where('id', $sessionPositionId)->value('name');

        return $positionName ?: 'crew';
    }
}
