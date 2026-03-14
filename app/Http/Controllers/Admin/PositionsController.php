<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Position;
use Illuminate\Http\Request;

class PositionsController extends Controller
{
    public function index(Request $request)
    {
        $query = Position::withCount('users');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $positions = $query->latest()->paginate(10)->withQueryString();

        return view('pages.positions', compact('positions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:100|unique:positions,slug',
            'level' => 'required|integer|min:1',
            'description' => 'nullable|string|max:1000',
        ]);

        Position::create($validated);

        return redirect()->route('positions')->with('success', 'Position added successfully.');
    }

    public function update(Request $request, Position $position)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:100|unique:positions,slug,' . $position->id,
            'level' => 'required|integer|min:1',
            'description' => 'nullable|string|max:1000',
        ]);

        $position->update($validated);

        return redirect()->route('positions')->with('success', 'Position updated successfully.');
    }

    public function destroy(Position $position)
    {
        $position->delete();

        return redirect()->route('positions')->with('success', 'Position deleted.');
    }
}
