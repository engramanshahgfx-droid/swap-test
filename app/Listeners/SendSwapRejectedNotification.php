<?php

namespace App\Listeners;

use App\Events\SwapRejected;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendSwapRejectedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function handle(SwapRejected $event)
    {
        $this->notificationService->sendSwapRejected($event->swapRequest, $event->rejectedBy, $event->reason);
    }
}
