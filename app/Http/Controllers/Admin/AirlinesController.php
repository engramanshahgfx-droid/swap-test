<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Airline;
use Illuminate\Http\Request;

class AirlinesController extends Controller
{
    public function index(Request $request)
    {
        $query = Airline::withCount('users');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('country', 'like', "%{$search}%");
            });
        }

        $airlines = $query->latest()->paginate(10)->withQueryString();

        return view('pages.airlines', compact('airlines'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'code'    => 'required|string|max:10|unique:airlines,code',
            'country' => 'nullable|string|max:100',
        ]);

        Airline::create($validated);

        return redirect()->route('airlines')->with('success', 'Airline added successfully.');
    }

    public function update(Request $request, Airline $airline)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'code'    => 'required|string|max:10|unique:airlines,code,' . $airline->id,
            'country' => 'nullable|string|max:100',
        ]);

        $airline->update($validated);

        return redirect()->route('airlines')->with('success', 'Airline updated successfully.');
    }

    public function destroy(Airline $airline)
    {
        $airline->delete();

        return redirect()->route('airlines')->with('success', 'Airline deleted.');
    }
}
