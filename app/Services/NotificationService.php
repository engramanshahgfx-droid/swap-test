<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\SwapRequestNotification;
use App\Notifications\SwapApprovedNotification;
use App\Notifications\SwapRejectedNotification;
use App\Notifications\NewMessageNotification;
use Illuminate\Support\Facades\Notification;

class NotificationService
{
    public function sendSwapRequest($swapRequest)
    {
        // Notify the trip owner
        $swapRequest->toUser->notify(new SwapRequestNotification($swapRequest, 'request_received'));
        
        // Notify managers
        $managers = User::role('crew_manager')->get();
        Notification::send($managers, new SwapRequestNotification($swapRequest, 'manager_notification'));
    }

    public function sendSwapApproved($swapRequest, $approvedBy)
    {
        // Notify both users
        $swapRequest->fromUser->notify(new SwapApprovedNotification($swapRequest, $approvedBy));
        $swapRequest->toUser->notify(new SwapApprovedNotification($swapRequest, $approvedBy));
        
        // If approved by manager, notify the owner
        if ($approvedBy === 'manager') {
            $swapRequest->toUser->notify(new SwapApprovedNotification($swapRequest, 'manager_final'));
        }
    }

    public function sendSwapRejected($swapRequest, $rejectedBy, $reason)
    {
        // Notify the requesting user
        $swapRequest->fromUser->notify(new SwapRejectedNotification($swapRequest, $rejectedBy, $reason));
        
        // If rejected by manager, also notify the owner
        if ($rejectedBy === 'manager') {
            $swapRequest->toUser->notify(new SwapRejectedNotification($swapRequest, $rejectedBy, $reason));
        }
    }

    public function sendNewMessage($message)
    {
        $conversation = $message->conversation;
        $recipient = $conversation->getOtherParticipant($message->sender_id);
        
        $recipient->notify(new NewMessageNotification($message));
    }
}
