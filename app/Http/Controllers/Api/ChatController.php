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
        $conversations = $this->chatService->getUserConversations($request->user());

        return response()->json([
            'success' => true,
            'data' => $conversations,
        ]);
    }

    public function messages(Request $request, $conversationId)
    {
        $conversation = Conversation::findOrFail($conversationId);

        // Check if user is part of this conversation
        if ($conversation->user_one_id !== $request->user()->id && 
            $conversation->user_two_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => __('messages.unauthorized'),
            ], 403);
        }

        $messages = $this->chatService->getConversationMessages($conversation, $request->user());

        return response()->json([
            'success' => true,
            'data' => $messages,
        ]);
    }

    public function sendMessage(SendMessageRequest $request)
    {
        $user = $request->user();
        
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
            $recipient = User::findOrFail($request->recipient_id);
            $conversation = $this->chatService->getOrCreateConversation($user, $recipient);
        }

        // Send message
        $message = $this->chatService->sendMessage(
            $conversation,
            $user,
            $request->message
        );

        return response()->json([
            'success' => true,
            'message' => __('messages.message_sent'),
            'data' => $message,
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
}
