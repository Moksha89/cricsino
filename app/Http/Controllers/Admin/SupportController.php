<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportConversation;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SupportController extends Controller
{
    public function index(Request $request)
    {
        $conversations = SupportConversation::query()
            ->with(['user:id,name,email', 'latestMessage'])
            ->withCount('messages')
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->category, fn ($q, $c) => $q->where('category', $c))
            ->when($request->search, function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    $q->where('subject', 'like', "%{$search}%")
                      ->orWhere('id', $search)
                      ->orWhereHas('user', fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
                });
            })
            ->latest('last_message_at')
            ->paginate(20);

        return Inertia::render('Admin/Support/Index', [
            'conversations' => $conversations,
            'filters' => $request->only(['status', 'category', 'search']),
            'categories' => SupportConversation::CATEGORIES,
            'statuses' => SupportConversation::STATUSES,
        ]);
    }

    public function show(SupportConversation $conversation)
    {
        $conversation->load(['messages.sender', 'user']);

        $conversation->messages()
            ->where('sender_type', 'user')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return Inertia::render('Admin/Support/Show', [
            'conversation' => $conversation,
            'statuses' => SupportConversation::STATUSES,
        ]);
    }

    public function reply(Request $request, SupportConversation $conversation)
    {
        $validated = $request->validate([
            'message' => 'required|string|min:1|max:5000',
        ]);

        $conversation->messages()->create([
            'sender_type' => 'admin',
            'sender_id' => $request->user()->id,
            'message' => $validated['message'],
        ]);

        $conversation->update([
            'status' => 'answered',
            'last_message_at' => now(),
        ]);

        NotificationService::supportAdminReplied($conversation);

        return back()->with('success', 'Reply sent.');
    }

    public function updateStatus(Request $request, SupportConversation $conversation)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:' . implode(',', SupportConversation::STATUSES),
        ]);

        $updates = ['status' => $validated['status']];

        if ($validated['status'] === 'closed') {
            $updates['closed_at'] = now();
        } elseif ($conversation->status === 'closed') {
            $updates['closed_at'] = null;
        }

        $conversation->update($updates);

        return back()->with('success', 'Status updated.');
    }
}
