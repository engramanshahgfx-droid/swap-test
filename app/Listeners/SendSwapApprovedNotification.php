<?php

namespace App\Listeners;

use App\Events\SwapApproved;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendSwapApprovedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function handle(SwapApproved $event)
    {
        $this->notificationService->sendSwapApproved($event->swapRequest, $event->approvedBy);
    }
}
