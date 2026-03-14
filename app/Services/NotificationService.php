<?php

namespace App\Services;

use App\Models\User;

class NotificationService
{
    public function __construct(private MobileNotificationService $mobileNotificationService)
    {
    }

    public function sendSwapRequest($swapRequest)
    {
        $requester = $swapRequest->requester;
        $responder = $swapRequest->responder;
        $flight = $swapRequest->publishedTrip?->flight;

        if ($responder) {
            $this->mobileNotificationService->createForUser(
                $responder,
                'New Swap Request',
                ($requester?->full_name ?? 'A crew member') . ' wants to swap your flight.',
                'swap',
                'swap_request_sound.mp3',
                [
                    'swap_request_id' => (string) $swapRequest->id,
                    'flight_number' => (string) ($flight?->flight_number ?? ''),
                ]
            );
        }

        $managers = User::role('crew_manager')->get();
        $this->mobileNotificationService->createForUsers(
            $managers,
            'Swap Request Pending Review',
            'A new swap request requires review.',
            'swap',
            'swap_request_sound.mp3',
            ['swap_request_id' => (string) $swapRequest->id]
        );
    }

    public function sendSwapApproved($swapRequest, $approvedBy)
    {
        $requester = $swapRequest->requester;
        $responder = $swapRequest->responder;

        if ($requester) {
            $this->mobileNotificationService->createForUser(
                $requester,
                'Swap Accepted',
                'Your swap request has been accepted.',
                'swap_accepted',
                'swap_request_sound.mp3',
                ['swap_request_id' => (string) $swapRequest->id, 'approved_by' => (string) $approvedBy]
            );
        }

        if ($responder) {
            $this->mobileNotificationService->createForUser(
                $responder,
                'Swap Updated',
                'A swap for your trip has been approved.',
                'swap_accepted',
                'swap_request_sound.mp3',
                ['swap_request_id' => (string) $swapRequest->id, 'approved_by' => (string) $approvedBy]
            );
        }
    }

    public function sendSwapRejected($swapRequest, $rejectedBy, $reason)
    {
        $requester = $swapRequest->requester;
        $responder = $swapRequest->responder;
        $isCanceled = $swapRequest->status === 'cancelled';

        if ($requester) {
            $this->mobileNotificationService->createForUser(
                $requester,
                $isCanceled ? 'Swap Canceled' : 'Swap Rejected',
                $isCanceled ? 'Your swap request has been canceled.' : 'Your swap request has been rejected.',
                $isCanceled ? 'swap_canceled' : 'swap_rejected',
                'swap_request_sound.mp3',
                [
                    'swap_request_id' => (string) $swapRequest->id,
                    'reason' => (string) ($reason ?? ''),
                    'rejected_by' => (string) $rejectedBy,
                ]
            );
        }

        if ($responder && $isCanceled) {
            $this->mobileNotificationService->createForUser(
                $responder,
                'Swap Canceled',
                'A pending swap request was canceled.',
                'swap_canceled',
                'swap_request_sound.mp3',
                ['swap_request_id' => (string) $swapRequest->id]
            );
        }
    }

    public function sendNewMessage($message)
    {
        $conversation = $message->conversation;
        $recipient = $conversation->getOtherParticipant($message->sender_id);

        if ($recipient) {
            $this->mobileNotificationService->createForUser(
                $recipient,
                'New Message',
                $message->sender->full_name . ' sent you a message',
                'chat',
                'chat_message_sound.mp3',
                [
                    'conversation_id' => (string) $conversation->id,
                    'message_id' => (string) $message->id,
                ]
            );
        }
    }
}
