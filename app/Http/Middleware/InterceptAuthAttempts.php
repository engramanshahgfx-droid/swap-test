<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InterceptAuthAttempts
{
    public function handle(Request $request, Closure $next)
    {
        // Hook into the Auth to track attempts
        if (!isset($GLOBALS['auth_logged'])) {
            $GLOBALS['auth_logged'] = true;
            
            // Create a wrapper around Auth facade
            $originalAttempt = Auth::class . '::attempt';
            \Log::info('Auth interception middleware initialized');
        }

        $response = $next($request);

        return $response;
    }
}
