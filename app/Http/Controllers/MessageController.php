<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMessageRequest;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $conversations = Message::where('sender_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->with(['sender', 'receiver', 'product'])
            ->latest()
            ->get()
            ->groupBy(function ($message) use ($userId) {
                return $message->sender_id === $userId
                    ? $message->receiver_id
                    : $message->sender_id;
            })
            ->map(function ($messages) use ($userId) {
                $latest = $messages->first();
                $otherUser = $latest->sender_id === $userId ? $latest->receiver : $latest->sender;
                $unread = $messages->where('receiver_id', $userId)->whereNull('read_at')->count();

                return [
                    'user' => $otherUser,
                    'lastMessage' => $latest,
                    'unreadCount' => $unread,
                ];
            })
            ->values();

        return Inertia::render('Messages/Index', [
            'conversations' => $conversations,
        ]);
    }

    public function show(Request $request, User $user)
    {
        $userId = $request->user()->id;

        $messages = Message::where(function ($q) use ($userId, $user) {
            $q->where('sender_id', $userId)->where('receiver_id', $user->id);
        })->orWhere(function ($q) use ($userId, $user) {
            $q->where('sender_id', $user->id)->where('receiver_id', $userId);
        })
            ->with(['sender', 'receiver', 'product'])
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark received messages as read
        Message::where('sender_id', $user->id)
            ->where('receiver_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return Inertia::render('Messages/Show', [
            'otherUser' => $user,
            'messages' => $messages,
        ]);
    }

    public function store(StoreMessageRequest $request)
    {
        Message::create([
            ...$request->validated(),
            'sender_id' => $request->user()->id,
        ]);

        return back()->with('success', 'Message sent');
    }
}
