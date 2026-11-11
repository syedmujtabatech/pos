<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\InternalMessage;
use App\Models\User;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'inbox');
        $userId = auth()->id();

        $unreadCount = InternalMessage::where('receiver_id', $userId)->where('is_read', false)->count();
        $totalReceived = InternalMessage::where('receiver_id', $userId)->count();
        $totalSent = InternalMessage::where('sender_id', $userId)->count();

        if ($tab === 'sent') {
            $query = InternalMessage::with('receiver.role')
                ->where('sender_id', $userId)
                ->latest('id');
        } else {
            $query = InternalMessage::with('sender.role')
                ->where('receiver_id', $userId)
                ->latest('id');

            if ($request->boolean('unread_only')) {
                $query->where('is_read', false);
            }
        }

        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('subject', 'like', $term)
                  ->orWhere('message', 'like', $term);
            });
        }

        $messages = $query->paginate(15)->appends($request->query());

        // Get all active colleagues with roles for message recipient selector
        $colleagues = User::with('role')
            ->where('id', '!=', $userId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('messages.index', compact('messages', 'colleagues', 'tab', 'unreadCount', 'totalReceived', 'totalSent'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'priority' => 'required|in:normal,urgent,info',
        ]);

        $receiver = User::findOrFail($request->receiver_id);

        $msg = InternalMessage::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $request->receiver_id,
            'subject' => $request->subject,
            'message' => $request->message,
            'priority' => $request->priority,
            'is_read' => false,
        ]);

        AuditLog::log('send_message', $msg, null, null, "Internal message sent to {$receiver->name} ({$receiver->username})");

        $roleName = $receiver->role->name ?? 'Staff';
        return back()->with('success', "Message sent successfully to {$receiver->name} ({$roleName}).");
    }

    public function show(InternalMessage $message)
    {
        $userId = auth()->id();
        if ($message->sender_id !== $userId && $message->receiver_id !== $userId) {
            abort(403, 'Unauthorized access to this message.');
        }

        if ($message->receiver_id === $userId && !$message->is_read) {
            $message->markAsRead();
        }

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => [
                    'id' => $message->id,
                    'subject' => $message->subject,
                    'body' => $message->message,
                    'priority' => $message->priority,
                    'created_at' => $message->created_at->format('d M Y, h:i A'),
                    'sender_id' => $message->sender_id,
                    'sender_name' => $message->sender->name ?? 'System',
                    'sender_role' => $message->sender->role->name ?? 'Staff',
                    'receiver_name' => $message->receiver->name ?? 'User',
                    'receiver_role' => $message->receiver->role->name ?? 'Staff',
                ]
            ]);
        }

        return redirect()->route('messages.index');
    }

    public function markAsRead(InternalMessage $message)
    {
        if ($message->receiver_id === auth()->id()) {
            $message->markAsRead();
        }

        return back()->with('success', 'Message marked as read.');
    }

    public function destroy(InternalMessage $message)
    {
        $userId = auth()->id();
        if ($message->sender_id !== $userId && $message->receiver_id !== $userId) {
            abort(403, 'Unauthorized.');
        }

        $message->delete();

        return back()->with('success', 'Message deleted successfully.');
    }
}
