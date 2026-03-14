<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLanguage
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $supportedLocales = ['en', 'ar'];
        $locale = null;

        // 1. Check query parameter first (highest priority)
        if ($request->has('lang') && in_array($request->get('lang'), $supportedLocales)) {
            $locale = $request->get('lang');
            session(['locale' => $locale]);
        }
        // 2. Check session for stored locale
        elseif ($locale = session('locale')) {
            if (!in_array($locale, $supportedLocales)) {
                $locale = null;
            }
        }
        // 3. Check Accept-Language header only for API requests.
        // For web/admin pages we keep English unless user explicitly switches.
        elseif ($request->is('api/*') && ($acceptLang = $request->header('Accept-Language'))) {
            $parsedLocale = substr($acceptLang, 0, 2);
            if (in_array($parsedLocale, $supportedLocales)) {
                $locale = $parsedLocale;
                session(['locale' => $locale]);
            }
        }
        // 4. Fall back to app default locale
        if (!$locale) {
            $locale = config('app.locale');
        }

        // Set the application locale
        app()->setLocale($locale);
        // Also set for HTTP responses
        session(['locale' => $locale]);

        return $next($request);
    }
}
