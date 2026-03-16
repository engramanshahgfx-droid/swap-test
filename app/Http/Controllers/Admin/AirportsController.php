<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Airport;
use Illuminate\Http\Request;

class AirportsController extends Controller
{
    public function index(Request $request)
    {
        $query = Airport::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('country', 'like', "%{$search}%");
            });
        }

        $airports = $query->latest()->paginate(15)->withQueryString();

        return view('pages.airports', compact('airports'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'code'    => 'required|string|max:10|unique:airports,code',
            'city'    => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
        ]);

        Airport::create($validated);

        return redirect()->route('airports')->with('success', 'Airport added successfully.');
    }

    public function update(Request $request, Airport $airport)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'code'    => 'required|string|max:10|unique:airports,code,' . $airport->id,
            'city'    => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
        ]);

        $airport->update($validated);

        return redirect()->route('airports')->with('success', 'Airport updated successfully.');
    }

    public function destroy(Airport $airport)
    {
        $airport->delete();

        return redirect()->route('airports')->with('success', 'Airport deleted successfully.');
    }
}
