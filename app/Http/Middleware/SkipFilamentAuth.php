<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SkipFilamentAuth
{
    public function handle(Request $request, Closure $next)
    {
        // This middleware ensures frontend routes are not affected by Filament authentication
        return $next($request);
    }
}
