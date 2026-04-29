<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Airline;
use App\Models\PlaneType;
use Illuminate\Http\Request;

class AircraftController extends Controller
{
    public function index(Request $request)
    {
        $query = PlaneType::with('airline');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhereHas('airline', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $aircrafts = $query->latest()->paginate(15)->withQueryString();
        $airlines = Airline::orderBy('name')->get();

        return view('pages.aircraft', compact('aircrafts', 'airlines'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:plane_types,code',
            'airline_id' => 'required|exists:airlines,id',
            'capacity' => 'nullable|integer|min:0',
            'is_active' => 'sometimes',
        ]);

        $validated['is_active'] = $request->has('is_active');

        PlaneType::create($validated);

        return redirect()->route('aircrafts')->with('success', 'Aircraft added successfully.');
    }

    public function update(Request $request, PlaneType $aircraft)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:plane_types,code,' . $aircraft->id,
            'airline_id' => 'required|exists:airlines,id',
            'capacity' => 'nullable|integer|min:0',
            'is_active' => 'sometimes',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $aircraft->update($validated);

        return redirect()->route('aircrafts')->with('success', 'Aircraft updated successfully.');
    }

    public function destroy(PlaneType $aircraft)
    {
        $aircraft->delete();

        return redirect()->route('aircrafts')->with('success', 'Aircraft deleted successfully.');
    }
}
