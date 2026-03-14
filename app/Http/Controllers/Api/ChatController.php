<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SendMessageRequest;
use App\Models\Conversation;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    protected $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    public function conversations(Request $request)
    {
        $perPage = max(1, min((int) $request->integer('per_page', 20), 100));
        $page = max(1, (int) $request->integer('page', 1));
        $conversations = $this->chatService->getUserConversations($request->user());

        $items = $conversations->forPage($page, $perPage)->values();

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $items,
                'pagination' => [
                    'current_page' => $page,
                    'last_page' => max(1, (int) ceil($conversations->count() / $perPage)),
                    'per_page' => $perPage,
                    'total' => $conversations->count(),
                ],
            ],
        ]);
    }

    public function messages(Request $request, $conversationId)
    {
        $conversation = Conversation::findOrFail($conversationId);
        $perPage = max(1, min((int) $request->integer('per_page', 50), 100));

        // Check if user is part of this conversation
        if ($conversation->user_one_id !== $request->user()->id && 
            $conversation->user_two_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => __('messages.unauthorized'),
            ], 403);
        }

        $messages = $this->chatService->getConversationMessages($conversation, $request->user(), $perPage);

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $messages->items(),
                'pagination' => [
                    'current_page' => $messages->currentPage(),
                    'last_page' => $messages->lastPage(),
                    'per_page' => $messages->perPage(),
                    'total' => $messages->total(),
                ],
            ],
        ]);
    }

    public function sendMessage(SendMessageRequest $request)
    {
        $user = $request->user();
        $messageType = $request->input('message_type', 'text');
        
        // Get conversation - either by ID or by recipient
        if ($request->conversation_id) {
            $conversation = Conversation::findOrFail($request->conversation_id);
            
            // Check if user is part of this conversation
            if ($conversation->user_one_id !== $user->id && 
                $conversation->user_two_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.unauthorized'),
                ], 403);
            }
        } else {
            $recipientId = $request->recipient_id ?? $request->receiver_id;
            $recipient = User::findOrFail($recipientId);
            $conversation = $this->chatService->getOrCreateConversation($user, $recipient);
        }

        // Send message
        $message = $this->chatService->sendMessage(
            $conversation,
            $user,
            $request->message,
            $messageType
        );

        $receiverId = $conversation->user_one_id === $user->id
            ? $conversation->user_two_id
            : $conversation->user_one_id;

        return response()->json([
            'success' => true,
            'message' => __('messages.message_sent'),
            'data' => [
                'message_id' => $message->id,
                'sender_id' => $message->sender_id,
                'receiver_id' => $receiverId,
                'message' => $message->body,
                'message_type' => $message->message_type ?? 'text',
                'delivery_status' => $message->read_at ? 'read' : 'delivered',
                'created_at' => $message->created_at,
            ],
        ], 201);
    }

    public function markAsRead(Request $request, $conversationId)
    {
        $conversation = Conversation::findOrFail($conversationId);

        // Check if user is part of this conversation
        if ($conversation->user_one_id !== $request->user()->id && 
            $conversation->user_two_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $this->chatService->markMessagesAsRead($conversation, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Messages marked as read',
        ]);
    }

    public function unreadCount(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'unread_count' => $this->chatService->getUnreadCount($request->user()),
            ],
        ]);
    }

    public function markRead(Request $request)
    {
        $validated = $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
        ]);

        return $this->markAsRead($request, $validated['conversation_id']);
    }
}
