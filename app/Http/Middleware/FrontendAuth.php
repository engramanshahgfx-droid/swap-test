<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class FrontendAuth
{
    public function handle(Request $request, Closure $next)
    {
        // Check if user is authenticated via Auth::user() OR via session (offline registration)
        if (!auth()->check() && !session('authenticated')) {
            return redirect()->route('frontend.login')->with('error', 'Please login first');
        }

        return $next($request);
    }
}
