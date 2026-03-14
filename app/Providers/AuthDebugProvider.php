<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\AuthManager;

class AuthDebugProvider extends ServiceProvider
{
    public function register(): void
    {
        // Hook into the auth manager
    }

    public function boot(): void
    {
        // Override the guard's attempt method
        $this->app['auth']->extend('debug-web', function ($app, $name, array $config) {
            $guard = $this->app['auth']->createGuard(array_merge($config, ['driver' => 'session']));
            
            // Wrap the attempt method
            $originalAttempt = [$guard, 'attempt'];
            
            $wrapped = function (...$args) use ($originalAttempt) {
                \Log::info('=== AUTH ATTEMPT CALLED ===', [
                    'credentials' => $args[0] ?? [],
                    'remember' => $args[1] ?? false,
                ]);
                
                $result = call_user_func_array($originalAttempt, $args);
                
                \Log::info('=== AUTH ATTEMPT RESULT ===', [
                    'result' => $result ? 'SUCCESS' : 'FAILURE',
                ]);
                
                return $result;
            };
            
            return $guard;
        });
    }
}
