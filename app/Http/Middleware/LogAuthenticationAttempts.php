<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class LogAuthenticationAttempts
{
    public function handle(Request $request, Closure $next)
    {
        if (str_contains($request->path(), 'admin/login')) {
            if ($request->isMethod('post')) {
                \Log::critical('LOGIN FORM SUBMISSION DETECTED', [
                    'path' => $request->path(),
                    'method' => $request->method(),
                    'all_data' => $request->all(),
                ]);
            }
        }

        return $next($request);
    }
}
