<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    public function index(Request $request)
    {
        $conversations = Conversation::with([
            'userOne',
            'userTwo',
        ])
        ->withCount('messages')
        ->latest('last_message_at')
        ->paginate(20);

        $selectedConversation = null;
        $selectedUser = null;
        $secondaryUser = null;
        $messages = collect();

        $selectedConversationId = $request->integer('conversation_id') ?: optional($conversations->first())->id;

        if ($selectedConversationId) {
            $selectedConversation = Conversation::with([
                'userOne.airline',
                'userOne.position',
                'userOne.trips.flight',
                'userTwo.airline',
                'userTwo.position',
                'userTwo.trips.flight',
            ])->findOrFail($selectedConversationId);

            $selectedUser = $selectedConversation->userOne;
            $secondaryUser = $selectedConversation->userTwo;
            $messages = $selectedConversation
                ? $selectedConversation->messages()->with('sender')->latest()->take(100)->get()->sortBy('created_at')->values()
                : collect();
        }

        return view('pages.support', compact('conversations', 'selectedConversation', 'selectedUser', 'secondaryUser', 'messages'));
    }

    public function reply(Request $request, Conversation $conversation)
    {
        $validated = $request->validate([
            'body' => 'required|string|max:2000',
        ]);

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $request->user()->id,
            'body' => $validated['body'],
        ]);

        $conversation->update([
            'last_message_at' => now(),
        ]);

        return redirect()->route('support', ['conversation_id' => $conversation->id])->with('success', 'Reply sent successfully.');
    }
}
