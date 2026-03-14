<?php

namespace App\Listeners;

use App\Events\SwapRequested;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendSwapRequestNotification implements ShouldQueue
{
    use InteractsWithQueue;

    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function handle(SwapRequested $event)
    {
        $this->notificationService->sendSwapRequest($event->swapRequest);
    }
}
