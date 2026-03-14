<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SettingsController extends Controller
{
    public function index(Request $request)
    {
        return view('pages.settings', [
            'admin' => $request->user(),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $request->user()->id,
            'language' => 'nullable|in:en,ar',
            'theme' => 'nullable|in:light,dark',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $user = $request->user();
        $user->full_name = $validated['full_name'];
        $user->email = $validated['email'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        if (!empty($validated['language'])) {
            session(['locale' => $validated['language']]);
        }

        if (!empty($validated['theme'])) {
            session(['admin_theme' => $validated['theme']]);
        }

        return redirect()->route('settings.page')->with('success', 'Settings updated successfully.');
    }
}
