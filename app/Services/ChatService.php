<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Models\PublishedTrip;
use App\Events\NewMessage;
use Illuminate\Support\Facades\DB;

class ChatService
{
    public function __construct(private MobileNotificationService $mobileNotificationService)
    {
    }

    public function getOrCreateConversation(User $user1, User $user2)
    {
        // Check if conversation already exists
        $conversation = Conversation::where(function ($query) use ($user1, $user2) {
            $query->where('user_one_id', $user1->id)
                  ->where('user_two_id', $user2->id);
        })->orWhere(function ($query) use ($user1, $user2) {
            $query->where('user_one_id', $user2->id)
                  ->where('user_two_id', $user1->id);
        })->first();

        // If not, create new conversation
        if (!$conversation) {
            $conversation = Conversation::create([
                'user_one_id' => $user1->id,
                'user_two_id' => $user2->id,
            ]);
        }

        return $conversation;
    }

    public function sendMessage(Conversation $conversation, User $sender, string $messageText, string $messageType = 'text', ?int $mentionedTripId = null)
    {
        return DB::transaction(function () use ($conversation, $sender, $messageText, $messageType) {
            $message = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $sender->id,
                'body' => $messageText,
                'message_type' => $messageType,
                'mentioned_trip_id' => null,
                'delivered_at' => now(),
            ]);

            // Update conversation last message time
            $conversation->update([
                'last_message_at' => now(),
            ]);

            // Broadcast event for real-time messaging safely
            try {
                broadcast(new NewMessage($message))->toOthers();
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Chat broadcast failed: ' . $e->getMessage());
            }

            $recipient = $conversation->getOtherParticipant($sender->id);
            if ($recipient) {
                try {
                    $this->mobileNotificationService->createForUser(
                        $recipient,
                        'New Message',
                        $sender->full_name . ': ' . mb_substr($messageText, 0, 120),
                        'chat',
                        'chat_message_sound.mp3',
                        [
                            'conversation_id' => (string) $conversation->id,
                            'message_id' => (string) $message->id,
                        ]
                    );
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('Chat mobile notification failed: ' . $e->getMessage());
                }
            }

            // Handle mentioned trip notifications
            if ($mentionedTripId) {
                try {
                    $trip = PublishedTrip::find($mentionedTripId);
                    if ($trip && $trip->user_id) {
                        $tripOwner = User::find($trip->user_id);
                        if ($tripOwner && $tripOwner->id !== $recipient?->id) {
                            $this->mobileNotificationService->createForUser(
                                $tripOwner,
                                'You were mentioned',
                                $sender->full_name . ' mentioned your trip #' . $trip->id,
                                'trip_mention',
                                'mention_sound.mp3',
                                [
                                    'published_trip_id' => (string) $trip->id,
                                    'conversation_id' => (string) $conversation->id,
                                    'message_id' => (string) $message->id,
                                ]
                            );
                        }
                    }
                } catch (\Throwable $e) {
                    // swallow; do not block message send for notification failures
                }
            }

            // If mentioned_trip_id provided, update message record
            if ($mentionedTripId) {
                $message->mentioned_trip_id = $mentionedTripId;
                $message->save();
            }

            return $message->load('sender');
        });
    }

    public function markMessagesAsRead(Conversation $conversation, User $user)
    {
        return Message::where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
            ]);
    }

    public function getUserConversations(User $user)
    {
        return Conversation::where('user_one_id', $user->id)
            ->orWhere('user_two_id', $user->id)
            ->with(['userOne', 'userTwo', 'messages' => function ($query) {
                $query->latest()->limit(1);
            }])
            ->orderBy('last_message_at', 'desc')
            ->get()
            ->map(function ($conversation) use ($user) {
                $otherUser = $conversation->getOtherParticipant($user->id);
                $lastMessage = $conversation->getLastMessageAttribute();
                $unreadCount = Message::where('conversation_id', $conversation->id)
                    ->where('sender_id', '!=', $user->id)
                    ->whereNull('read_at')
                    ->count();

                return [
                    'id' => $conversation->id,
                    'other_user' => [
                        'id' => $otherUser->id,
                        'name' => $otherUser->full_name,
                        'employee_id' => $otherUser->employee_id,
                        'position' => $otherUser->position->name ?? 'Unknown',
                    ],
                    'last_message' => $lastMessage ? [
                        'message_id' => $lastMessage->id,
                        'sender_id' => $lastMessage->sender_id,
                        'receiver_id' => $otherUser->id,
                        'message' => $lastMessage->body,
                        'message_type' => $lastMessage->message_type ?? 'text',
                        'delivery_status' => $this->resolveDeliveryStatus($lastMessage),
                        'created_at' => $lastMessage->created_at,
                        'is_read' => $lastMessage->read_at !== null,
                    ] : null,
                    'unread_count' => $unreadCount,
                    'updated_at' => $conversation->updated_at,
                ];
            });
    }

    public function getConversationMessages(Conversation $conversation, User $user, int $perPage = 50)
    {
        // Mark messages as read
        $this->markMessagesAsRead($conversation, $user);

        return Message::where('conversation_id', $conversation->id)
            ->with('sender')
            ->orderBy('created_at', 'asc')
            ->paginate($perPage)
            ->through(function ($message) use ($user, $conversation) {
                $receiverId = $conversation->user_one_id === $message->sender_id
                    ? $conversation->user_two_id
                    : $conversation->user_one_id;

                return [
                    'message_id' => $message->id,
                    'sender_id' => $message->sender_id,
                    'receiver_id' => $receiverId,
                    'message' => $message->body,
                    'message_type' => $message->message_type ?? 'text',
                    'delivery_status' => $this->resolveDeliveryStatus($message),
                    'sender' => [
                        'id' => $message->sender->id,
                        'name' => $message->sender->full_name,
                    ],
                    'is_me' => $message->sender_id === $user->id,
                    'is_read' => $message->read_at !== null,
                    'created_at' => $message->created_at,
                ];
            });
    }

    public function getUnreadCount(User $user): int
    {
        return Message::whereHas('conversation', function ($query) use ($user) {
            $query->where('user_one_id', $user->id)
                ->orWhere('user_two_id', $user->id);
        })
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->count();
    }

    protected function resolveDeliveryStatus(Message $message): string
    {
        if ($message->read_at) {
            return 'read';
        }

        if ($message->delivered_at) {
            return 'delivered';
        }

        return 'sent';
    }
}
