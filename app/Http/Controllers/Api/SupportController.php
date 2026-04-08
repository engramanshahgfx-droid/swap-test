<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Services\ChatService;
use App\Services\MobileNotificationService;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    public function __construct(
        private ChatService $chatService,
        private MobileNotificationService $mobileNotificationService
    ) {
    }

    /**
     * Get or create support conversation with admin
     */
    public function getSupportConversation(Request $request)
    {
        $user = $request->user();
        
        // Find admin user (assuming first admin or specific support user)
        $supportAdmin = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['admin', 'super-admin', 'support_moderator']);
        })->first();

        if (!$supportAdmin) {
            return response()->json([
                'success' => false,
                'message' => 'No support admin available',
            ], 404);
        }

        $conversation = $this->chatService->getOrCreateConversation($user, $supportAdmin);

        return response()->json([
            'success' => true,
            'data' => [
                'conversation_id' => $conversation->id,
                'support_admin' => [
                    'id' => $supportAdmin->id,
                    'name' => $supportAdmin->full_name,
                    'email' => $supportAdmin->email,
                ],
            ],
        ]);
    }

    /**
     * Send message to support
     */
    public function sendSupportMessage(Request $request)
    {
        $validated = $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'message' => 'required|string|max:2000',
            'message_type' => 'nullable|string|in:text,image,audio,file',
        ]);

        $conversation = Conversation::findOrFail($validated['conversation_id']);
        $user = $request->user();

        // Verify user is part of this conversation
        if ($conversation->user_one_id !== $user->id && $conversation->user_two_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Verify at least one participant is an admin
        $otherUser = $conversation->getOtherParticipant($user->id);
        $isAdminConversation = $user->hasAnyRole(['admin', 'super-admin', 'support_moderator']) ||
                               $otherUser->hasAnyRole(['admin', 'super-admin', 'support_moderator']);

        if (!$isAdminConversation) {
            return response()->json([
                'success' => false,
                'message' => 'This conversation is not a support conversation',
            ], 422);
        }

        // Send message
        $message = $this->chatService->sendMessage(
            $conversation,
            $user,
            $validated['message'],
            $validated['message_type'] ?? 'text'
        );

        return response()->json([
            'success' => true,
            'message' => 'Support message sent',
            'data' => [
                'message_id' => $message->id,
                'sender_id' => $message->sender_id,
                'message' => $message->body,
                'message_type' => $message->message_type ?? 'text',
                'created_at' => $message->created_at,
            ],
        ], 201);
    }

    /**
     * Get support conversation messages
     */
    public function getSupportMessages(Request $request, $conversationId)
    {
        $conversation = Conversation::findOrFail($conversationId);
        $user = $request->user();

        // Verify user is part of this conversation
        if ($conversation->user_one_id !== $user->id && $conversation->user_two_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $perPage = max(1, min((int) $request->integer('per_page', 50), 100));
        $messages = $this->chatService->getConversationMessages($conversation, $user, $perPage);

        return response()->json([
            'success' => true,
            'data' => [
                'conversation_id' => $conversation->id,
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

    /**
     * List all support conversations for admin
     */
    public function listSupportConversations(Request $request)
    {
        if (!$request->user()->hasAnyRole(['admin', 'super-admin', 'support_moderator'])) {
            return response()->json([
                'success' => false,
                'message' => 'Only support staff can access this',
            ], 403);
        }

        $conversations = Conversation::with(['userOne', 'userTwo', 'messages' => function ($query) {
            $query->latest()->limit(1);
        }])
            ->withCount('messages')
            ->latest('last_message_at')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $conversations->items(),
                'pagination' => [
                    'current_page' => $conversations->currentPage(),
                    'last_page' => $conversations->lastPage(),
                    'per_page' => $conversations->perPage(),
                    'total' => $conversations->total(),
                ],
            ],
        ]);
    }
}
