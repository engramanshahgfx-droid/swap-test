<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use App\Services\SmsService;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind SmsService to the container
        $this->app->singleton(SmsService::class, function ($app) {
            return new SmsService();
        });
    }

    public function boot(): void
    {
        // Validate Firebase config in non-local environments without crashing unrelated pages.
        if (!app()->environment(['local', 'testing'])) {
            $requiredConfig = [
                'services.firebase.api_key' => 'FIREBASE_API_KEY',
                'services.firebase.project_id' => 'FIREBASE_PROJECT_ID',
                'services.firebase.auth_domain' => 'FIREBASE_AUTH_DOMAIN',
                'services.firebase.service_account_key_path' => 'FIREBASE_SERVICE_ACCOUNT_KEY_PATH',
            ];

            $missing = [];

            foreach ($requiredConfig as $configPath => $envName) {
                if (blank(config($configPath))) {
                    $missing[] = $envName;
                }
            }

            if (!empty($missing)) {
                Log::warning('Firebase configuration incomplete. Firebase features will stay disabled until env is fixed.', [
                    'missing' => $missing,
                ]);
            } else {
                $serviceAccountPath = config('services.firebase.service_account_key_path');
                $resolvedServiceAccountPath = $this->resolveFirebaseServiceAccountPath($serviceAccountPath);

                if (!is_string($resolvedServiceAccountPath) || !is_file($resolvedServiceAccountPath)) {
                    Log::warning('Invalid FIREBASE_SERVICE_ACCOUNT_KEY_PATH. Firebase features will stay disabled until path is fixed.', [
                        'path' => (string) $serviceAccountPath,
                    ]);
                } else {
                    // Ensure downstream Firebase services always receive an absolute path.
                    config(['services.firebase.service_account_key_path' => $resolvedServiceAccountPath]);
                }
            }
        }

        Paginator::defaultView('vendor.pagination.admin');
        Paginator::defaultSimpleView('vendor.pagination.simple-default');
    }

    private function resolveFirebaseServiceAccountPath(mixed $path): ?string
    {
        if (!is_string($path) || $path === '') {
            return null;
        }

        if ($this->isAbsolutePath($path)) {
            return $path;
        }

        return base_path($path);
    }

    private function isAbsolutePath(string $path): bool
    {
        return preg_match('/^(?:[A-Za-z]:[\\\\\/]|\\\\\\\\|\/)/', $path) === 1;
    }
}
