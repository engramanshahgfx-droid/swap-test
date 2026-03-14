<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    /**
     * Set the application language
     *
     * @param Request $request
     * @param string $lang
     * @return \Illuminate\Http\JsonResponse
     */
    public function setLanguage($lang)
    {
        $supportedLocales = ['en', 'ar'];

        if (!in_array($lang, $supportedLocales)) {
            return response()->json([
                'success' => false,
                'message' => 'Unsupported language. Supported languages: ' . implode(', ', $supportedLocales),
            ], 400);
        }

        session(['locale' => $lang]);
        app()->setLocale($lang);

        return response()->json([
            'success' => true,
            'message' => __('messages.operation_successful'),
            'locale' => $lang,
            'supported_languages' => [
                [
                    'code' => 'en',
                    'name' => 'English'
                ],
                [
                    'code' => 'ar',
                    'name' => 'العربية'
                ]
            ]
        ]);
    }

    /**
     * Get the current language
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCurrentLanguage()
    {
        return response()->json([
            'locale' => app()->getLocale(),
            'fallback_locale' => config('app.fallback_locale'),
            'supported_languages' => [
                [
                    'code' => 'en',
                    'name' => 'English'
                ],
                [
                    'code' => 'ar',
                    'name' => 'العربية'
                ]
            ]
        ]);
    }

    /**
     * Get all supported languages
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSupportedLanguages()
    {
        return response()->json([
            'success' => true,
            'supported_languages' => [
                [
                    'code' => 'en',
                    'name' => 'English',
                    'native_name' => 'English',
                    'direction' => 'ltr'
                ],
                [
                    'code' => 'ar',
                    'name' => 'Arabic',
                    'native_name' => 'العربية',
                    'direction' => 'rtl'
                ]
            ]
        ]);
    }
}
