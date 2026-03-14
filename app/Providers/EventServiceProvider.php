<?php

namespace App\Providers;

use App\Events\SwapRequested;
use App\Events\SwapApproved;
use App\Events\SwapRejected;
use App\Events\SwapCompleted;
use App\Listeners\SendSwapRequestNotification;
use App\Listeners\SendSwapApprovedNotification;
use App\Listeners\SendSwapRejectedNotification;
use App\Listeners\SendSwapCompletedNotification;
use Illuminate\Auth\Events\Attempting;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        Attempting::class => [
            \App\Listeners\LogAuthenticationEvents::class,
        ],
        Login::class => [
            \App\Listeners\LogAuthenticationEvents::class,
        ],
        Failed::class => [
            \App\Listeners\LogAuthenticationEvents::class,
        ],
        SwapRequested::class => [
            SendSwapRequestNotification::class,
        ],
        SwapApproved::class => [
            SendSwapApprovedNotification::class,
        ],
        SwapRejected::class => [
            SendSwapRejectedNotification::class,
        ],
        SwapCompleted::class => [
            SendSwapCompletedNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
