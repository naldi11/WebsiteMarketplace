<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ChatControllerApi extends Controller
{
    /**
     * Get list of conversations (Contacts)
     */
    public function conversations(Request $request)
    {
        $user = $request->user();

        // Get unique users who have sent or received messages from current user
        $userIds = Message::where('sender_id', $user->id)
            ->orWhere('receiver_id', $user->id)
            ->get()
            ->map(function ($msg) use ($user) {
                return $msg->sender_id === $user->id ? $msg->receiver_id : $msg->sender_id;
            })
            ->unique();

        $contacts = User::whereIn('id', $userIds)
            ->get(['id', 'name', 'avatar'])
            ->map(function ($contact) use ($user) {
                // Get last message between current user and this contact
                $lastMessage = Message::where(function ($q) use ($user, $contact) {
                        $q->where('sender_id', $user->id)->where('receiver_id', $contact->id);
                    })
                    ->orWhere(function ($q) use ($user, $contact) {
                        $q->where('sender_id', $contact->id)->where('receiver_id', $user->id);
                    })
                    ->orderBy('created_at', 'desc')
                    ->first();

                $contact->last_message = $lastMessage ? $lastMessage->message : '';
                $contact->last_message_at = $lastMessage ? $lastMessage->created_at : null;
                $contact->unread_count = Message::where('sender_id', $contact->id)
                    ->where('receiver_id', $user->id)
                    ->where('is_read', false)
                    ->count();
                return $contact;
            })
            ->sortByDesc('last_message_at')
            ->values();

        return response()->json([
            'status' => 'success',
            'data' => $contacts
        ]);
    }

    /**
     * Get messages between current user and another user
     */
    public function messages(Request $request, $otherUserId)
    {
        $user = $request->user();

        $messages = Message::where(function ($query) use ($user, $otherUserId) {
                $query->where('sender_id', $user->id)
                      ->where('receiver_id', $otherUserId);
            })
            ->orWhere(function ($query) use ($user, $otherUserId) {
                $query->where('sender_id', $otherUserId)
                      ->where('receiver_id', $user->id);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark as read
        Message::where('sender_id', $otherUserId)
            ->where('receiver_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'status' => 'success',
            'data' => $messages
        ]);
    }

    /**
     * Send a new message
     */
    public function send(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'receiver_id' => 'required|exists:users,id',
            'message' => 'nullable|string',
            'attachment' => 'nullable|image|max:5120', // Max 5MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ], 422);
        }

        if (empty($request->message) && !$request->hasFile('attachment')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pesan atau lampiran harus diisi.'
            ], 422);
        }

        $user = $request->user();

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('chat_attachments', 'public');
        }

        $message = Message::create([
            'sender_id'   => $user->id,
            'receiver_id' => $request->receiver_id,
            'message'     => $request->message ?? '',
            'attachment'  => $attachmentPath,
            'is_read'     => 0
        ]);

        return response()->json([
            'status' => 'success',
            'data'   => [
                'id'          => $message->id,
                'sender_id'   => $message->sender_id,
                'receiver_id' => $message->receiver_id,
                'message'     => $message->message,
                'attachment'  => $message->attachment,
                'is_read'     => (int) $message->is_read,
                'created_at'  => $message->created_at,
                'updated_at'  => $message->updated_at,
            ]
        ]);
    }

    /**
     * Poll for new messages (unread)
     */
    public function poll(Request $request)
    {
        $user = $request->user();
        
        $newMessages = Message::where('receiver_id', $user->id)
            ->where('is_read', false)
            ->with('sender:id,name,avatar')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $newMessages
        ]);
    }
}
