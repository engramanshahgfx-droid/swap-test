<?php

namespace App\Listeners;

use App\Events\SwapCompleted;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendSwapCompletedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function handle(SwapCompleted $event)
    {
        $this->notificationService->sendSwapApproved($event->swapRequest, 'completed');
    }
}
