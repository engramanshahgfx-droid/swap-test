<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Attempting;

class LogAuthenticationEvents
{
    public function handle($event): void
    {
        if ($event instanceof Attempting) {
            \Log::critical('=== AUTH ATTEMPTING ===', $event->credentials);
        } elseif ($event instanceof Login) {
            \Log::info('=== AUTH LOGIN SUCCESS ===', [
                'user_id' => $event->user->id ?? null,
                'user_email' => $event->user->email ?? null,
            ]);
        } elseif ($event instanceof Failed) {
            \Log::error('=== AUTH FAILED ===', [
                'guard' => $event->guard,
            ]);
        }
    }
}
