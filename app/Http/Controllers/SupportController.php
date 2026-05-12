<?php

namespace App\Http\Controllers;

use App\Models\SupportConversation;
use App\Models\SupportMessage;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SupportController extends Controller
{
    public function index(Request $request)
    {
        $conversations = $request->user()
            ->supportConversations()
            ->withCount('messages')
            ->with('latestMessage')
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->category, fn ($q, $c) => $q->where('category', $c))
            ->latest('last_message_at')
            ->paginate(15);

        return Inertia::render('Support/Index', [
            'conversations' => $conversations,
            'filters' => $request->only(['status', 'category']),
            'categories' => SupportConversation::CATEGORIES,
            'statuses' => SupportConversation::STATUSES,
        ]);
    }

    public function create()
    {
        return Inertia::render('Support/Create', [
            'categories' => SupportConversation::CATEGORIES,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'category' => 'required|string|in:' . implode(',', SupportConversation::CATEGORIES),
            'message' => 'required|string|min:10|max:5000',
        ]);

        $conversation = $request->user()->supportConversations()->create([
            'subject' => $validated['subject'],
            'category' => $validated['category'],
            'status' => 'open',
            'last_message_at' => now(),
        ]);

        $conversation->messages()->create([
            'sender_type' => 'user',
            'sender_id' => $request->user()->id,
            'message' => $validated['message'],
        ]);

        NotificationService::supportTicketCreated($conversation);

        return redirect()->route('support.show', $conversation)
            ->with('success', 'Support ticket created successfully.');
    }

    public function show(Request $request, SupportConversation $conversation)
    {
        if ($conversation->user_id !== $request->user()->id) {
            abort(403);
        }

        $conversation->load(['messages.sender', 'user']);

        $conversation->messages()
            ->where('sender_type', 'admin')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return Inertia::render('Support/Show', [
            'conversation' => $conversation,
        ]);
    }

    public function reply(Request $request, SupportConversation $conversation)
    {
        if ($conversation->user_id !== $request->user()->id) {
            abort(403);
        }

        if ($conversation->status === 'closed') {
            return back()->with('error', 'This conversation is closed.');
        }

        $validated = $request->validate([
            'message' => 'required|string|min:1|max:5000',
        ]);

        $conversation->messages()->create([
            'sender_type' => 'user',
            'sender_id' => $request->user()->id,
            'message' => $validated['message'],
        ]);

        $conversation->update([
            'status' => 'pending',
            'last_message_at' => now(),
        ]);

        NotificationService::supportUserReplied($conversation);

        return back()->with('success', 'Reply sent.');
    }

    public function close(Request $request, SupportConversation $conversation)
    {
        if ($conversation->user_id !== $request->user()->id) {
            abort(403);
        }

        $conversation->update([
            'status' => 'closed',
            'closed_at' => now(),
        ]);

        return back()->with('success', 'Conversation closed.');
    }
}
