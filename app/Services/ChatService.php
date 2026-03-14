<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Events\NewMessage;
use Illuminate\Support\Facades\DB;

class ChatService
{
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

    public function sendMessage(Conversation $conversation, User $sender, $messageText)
    {
        return DB::transaction(function () use ($conversation, $sender, $messageText) {
            $message = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $sender->id,
                'body' => $messageText,
            ]);

            // Update conversation last message time
            $conversation->update([
                'last_message_at' => now(),
            ]);

            // Broadcast event for real-time messaging
            broadcast(new NewMessage($message))->toOthers();

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
                        'id' => $lastMessage->id,
                        'message' => $lastMessage->body,
                        'created_at' => $lastMessage->created_at,
                        'is_read' => $lastMessage->read_at !== null,
                        'sender_id' => $lastMessage->sender_id,
                    ] : null,
                    'unread_count' => $unreadCount,
                    'updated_at' => $conversation->updated_at,
                ];
            });
    }

    public function getConversationMessages(Conversation $conversation, User $user)
    {
        // Mark messages as read
        $this->markMessagesAsRead($conversation, $user);

        return Message::where('conversation_id', $conversation->id)
            ->with('sender')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($message) use ($user) {
                return [
                    'id' => $message->id,
                    'message' => $message->body,
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
}
