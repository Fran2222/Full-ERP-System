<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\Chat\ChatConversation;
use App\Models\Chat\ChatMessage;
use App\Models\Chat\ChatMessageAttachment;
use App\Models\Chat\ChatParticipant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ChatController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $this->touchPresence($user->id);

        $conversations = ChatConversation::query()
            ->with(['participantUsers', 'latestMessage.sender', 'latestMessage.attachments'])
            ->whereHas('participants', fn ($query) => $query->where('user_id', $user->id))
            ->get()
            ->sortByDesc(function ($conversation) {
                return optional($conversation->latestMessage)->created_at ?? $conversation->created_at;
            })
            ->values();

        $participantRows = ChatParticipant::where('user_id', $user->id)
            ->whereIn('chat_conversation_id', $conversations->pluck('id'))
            ->get()
            ->keyBy('chat_conversation_id');

        $unreadCounts = [];

        foreach ($conversations as $conversation) {
            $participant = $participantRows->get($conversation->id);
            $lastReadAt = optional($participant)->last_read_at;

            $unreadCounts[$conversation->id] = ChatMessage::where('chat_conversation_id', $conversation->id)
                ->where('sender_id', '!=', $user->id)
                ->when($lastReadAt, fn ($query) => $query->where('created_at', '>', $lastReadAt))
                ->count();
        }

        $users = User::query()
            ->where('id', '!=', $user->id)
            ->orderByRaw('CASE WHEN last_seen_at >= ? THEN 0 ELSE 1 END', [now()->subMinutes(2)])
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->orderBy('email')
            ->get();

        return view('chat.index', compact('conversations', 'users', 'unreadCounts'));
    }


    public function feed(Request $request)
    {
        $user = $request->user();

        $this->touchPresence($user->id);

        $onlineCutoff = now()->subMinutes(2);

        $conversations = ChatConversation::query()
            ->with(['participantUsers', 'latestMessage.sender', 'latestMessage.attachments'])
            ->whereHas('participants', fn ($query) => $query->where('user_id', $user->id))
            ->get()
            ->sortByDesc(function ($conversation) {
                return optional($conversation->latestMessage)->created_at ?? $conversation->created_at;
            })
            ->values();

        $participantRows = ChatParticipant::where('user_id', $user->id)
            ->whereIn('chat_conversation_id', $conversations->pluck('id'))
            ->get()
            ->keyBy('chat_conversation_id');

        $conversationPayload = $conversations->map(function ($conversation) use ($user, $participantRows, $onlineCutoff) {
            $other = $conversation->otherParticipantFor($user->id);
            $latest = $conversation->latestMessage;
            $participant = $participantRows->get($conversation->id);
            $lastReadAt = optional($participant)->last_read_at;

            $unread = ChatMessage::where('chat_conversation_id', $conversation->id)
                ->where('sender_id', '!=', $user->id)
                ->when($lastReadAt, fn ($query) => $query->where('created_at', '>', $lastReadAt))
                ->count();

            $displayName = $conversation->displayTitleFor($user->id);

            $latestPreview = 'No messages yet.';

            if ($latest) {
                if (trim((string) $latest->body) !== '') {
                    $senderName = $latest->sender
                        ? ($latest->sender->full_name ?: $latest->sender->email ?: 'User')
                        : 'User';

                    $latestPreview = $senderName . ': ' . $latest->body;
                } elseif ($latest->attachments && $latest->attachments->count()) {
                    $latestPreview = 'Sent an attachment';
                }
            }

            $isOnline = $other && $other->last_seen_at && $other->last_seen_at->greaterThanOrEqualTo($onlineCutoff);

            $presence = 'Offline';

            if ($other && $other->last_seen_at) {
                $presence = $isOnline ? 'Active now' : 'Active ' . $other->last_seen_at->diffForHumans();
            }

            return [
                'id' => $conversation->id,
                'url' => route('chat.show', $conversation),
                'name' => $displayName,
                'initial' => strtoupper(substr($displayName, 0, 1)),
                'email' => optional($other)->email,
                'latest_preview' => $latestPreview,
                'unread' => (int) $unread,
                'is_online' => (bool) $isOnline,
                'presence' => $presence,
                'search' => strtolower($displayName . ' ' . optional($other)->email . ' ' . $latestPreview),
            ];
        })->values();

        $peoplePayload = User::query()
            ->where('id', '!=', $user->id)
            ->orderByRaw('CASE WHEN last_seen_at >= ? THEN 0 ELSE 1 END', [$onlineCutoff])
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->orderBy('email')
            ->get()
            ->map(function ($chatUser) use ($onlineCutoff) {
                $displayName = $chatUser->full_name ?: $chatUser->email ?: 'User';
                $isOnline = $chatUser->last_seen_at && $chatUser->last_seen_at->greaterThanOrEqualTo($onlineCutoff);

                return [
                    'id' => $chatUser->id,
                    'name' => $displayName,
                    'initial' => strtoupper(substr($displayName, 0, 1)),
                    'email' => $chatUser->email,
                    'is_online' => (bool) $isOnline,
                    'presence' => ! $chatUser->last_seen_at ? 'Offline' : ($isOnline ? 'Active now' : 'Active ' . $chatUser->last_seen_at->diffForHumans()),
                    'search' => strtolower($displayName . ' ' . $chatUser->email),
                ];
            })
            ->values();

        return response()->json([
            'ok' => true,
            'conversations' => $conversationPayload,
            'people' => $peoplePayload,
        ]);
    }

    public function presencePing(Request $request)
    {
        if ($request->user()) {
            $this->touchPresence($request->user()->id);
        }

        return response()->json([
            'ok' => true,
            'server_time' => now()->toDateTimeString(),
        ]);
    }

    public function unreadCount(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'ok' => true,
                'unread_count' => 0,
            ]);
        }

        $this->touchPresence($user->id);

        $count = 0;

        $participantRows = ChatParticipant::where('user_id', $user->id)
            ->get(['chat_conversation_id', 'last_read_at']);

        foreach ($participantRows as $participantRow) {
            $count += ChatMessage::where('chat_conversation_id', $participantRow->chat_conversation_id)
                ->where('sender_id', '!=', $user->id)
                ->when($participantRow->last_read_at, function ($query) use ($participantRow) {
                    $query->where('created_at', '>', $participantRow->last_read_at);
                })
                ->count();
        }

        return response()->json([
            'ok' => true,
            'unread_count' => (int) $count,
        ]);
    }

    public function start(Request $request)
    {
        $this->touchPresence($request->user()->id);

        $data = $request->validate([
            'recipient_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $currentUserId = (int) $request->user()->id;
        $recipientId = (int) $data['recipient_id'];

        abort_if($currentUserId === $recipientId, 422, 'You cannot start a chat with yourself.');

        $conversation = DB::transaction(function () use ($currentUserId, $recipientId) {
            $conversationId = ChatParticipant::query()
                ->select('chat_conversation_id')
                ->whereIn('user_id', [$currentUserId, $recipientId])
                ->groupBy('chat_conversation_id')
                ->havingRaw('COUNT(DISTINCT user_id) = 2')
                ->pluck('chat_conversation_id')
                ->first();

            if ($conversationId) {
                $existing = ChatConversation::where('type', 'direct')->find($conversationId);

                if ($existing) {
                    return $existing;
                }
            }

            $conversation = ChatConversation::create([
                'type' => 'direct',
                'created_by' => $currentUserId,
            ]);

            ChatParticipant::create([
                'chat_conversation_id' => $conversation->id,
                'user_id' => $currentUserId,
                'last_read_at' => now(),
            ]);

            ChatParticipant::create([
                'chat_conversation_id' => $conversation->id,
                'user_id' => $recipientId,
                'last_read_at' => null,
            ]);

            return $conversation;
        });

        return redirect()->route('chat.show', $conversation);
    }

    public function show(Request $request, ChatConversation $conversation)
    {
        $user = $request->user();

        $this->touchPresence($user->id);
        $this->ensureParticipant($conversation, $user->id);

        ChatParticipant::where('chat_conversation_id', $conversation->id)
            ->where('user_id', $user->id)
            ->update(['last_read_at' => now()]);

        $conversation->load(['participantUsers']);

        return view('chat.show', [
            'conversation' => $conversation,
            'messages' => $conversation->messages()->with(['sender', 'attachments'])->oldest()->get(),
        ]);
    }

    public function send(Request $request, ChatConversation $conversation)
    {
        $user = $request->user();

        $this->touchPresence($user->id);
        $this->ensureParticipant($conversation, $user->id);

        $data = $request->validate([
            'body' => ['nullable', 'string', 'max:5000'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => [
                'file',
                'max:25600',
                'mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,zip,rar,mp4,mov,avi,webm,mkv',
            ],
        ]);

        $body = trim((string) ($data['body'] ?? ''));
        $files = $request->file('attachments', []);

        if ($body === '' && empty($files)) {
            return $request->ajax() || $request->expectsJson()
                ? response()->json(['ok' => false, 'message' => 'Please type a message or attach a file.'], 422)
                : back()->withErrors(['body' => 'Please type a message or attach a file.']);
        }

        $message = DB::transaction(function () use ($conversation, $user, $body, $files) {
            $message = ChatMessage::create([
                'chat_conversation_id' => $conversation->id,
                'sender_id' => $user->id,
                'body' => $body,
            ]);

            foreach ($files as $file) {
                if (! $file || ! $file->isValid()) {
                    continue;
                }

                $path = $file->store('chat-attachments/' . $conversation->id, 'public');

                ChatMessageAttachment::create([
                    'chat_message_id' => $message->id,
                    'disk' => 'public',
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size_bytes' => $file->getSize(),
                ]);
            }

            ChatParticipant::where('chat_conversation_id', $conversation->id)
                ->where('user_id', $user->id)
                ->update(['last_read_at' => now()]);

            return $message->fresh(['sender', 'attachments']);
        });

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => $this->formatMessagePayload($message, $user->id),
            ]);
        }

        return redirect()->route('chat.show', $conversation);
    }


    public function destroyMessage(Request $request, ChatConversation $conversation, ChatMessage $message)
    {
        $user = $request->user();

        $this->touchPresence($user->id);
        $this->ensureParticipant($conversation, $user->id);

        abort_unless((int) $message->chat_conversation_id === (int) $conversation->id, 404);
        abort_unless((int) $message->sender_id === (int) $user->id, 403);

        $message->delete();

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'deleted_id' => $message->id,
            ]);
        }

        return redirect()->route('chat.show', $conversation);
    }


    public function deletedIds(Request $request, ChatConversation $conversation)
    {
        $user = $request->user();

        $this->touchPresence($user->id);
        $this->ensureParticipant($conversation, $user->id);

        $ids = ChatMessage::withTrashed()
            ->where('chat_conversation_id', $conversation->id)
            ->whereNotNull('deleted_at')
            ->latest('deleted_at')
            ->limit(100)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        return response()->json([
            'ok' => true,
            'deleted_ids' => $ids,
        ]);
    }

    public function messages(Request $request, ChatConversation $conversation)
    {
        $user = $request->user();

        $this->touchPresence($user->id);
        $this->ensureParticipant($conversation, $user->id);

        $afterId = (int) $request->query('after_id', 0);

        $messages = ChatMessage::query()
            ->with(['sender', 'attachments'])
            ->where('chat_conversation_id', $conversation->id)
            ->when($afterId > 0, fn ($query) => $query->where('id', '>', $afterId))
            ->oldest()
            ->limit(50)
            ->get()
            ->map(fn ($message) => $this->formatMessagePayload($message, $user->id))
            ->values();

        if ($messages->isNotEmpty()) {
            ChatParticipant::where('chat_conversation_id', $conversation->id)
                ->where('user_id', $user->id)
                ->update(['last_read_at' => now()]);
        }

        return response()->json([
            'ok' => true,
            'messages' => $messages,
            'latest_id' => $messages->max('id') ?: $afterId,
            'read_statuses' => $this->recentReadStatuses($conversation, $user->id),
        ]);
    }

    private function ensureParticipant(ChatConversation $conversation, int $userId): void
    {
        abort_unless(
            ChatParticipant::where('chat_conversation_id', $conversation->id)
                ->where('user_id', $userId)
                ->exists(),
            403
        );
    }

    private function touchPresence(int $userId): void
    {
        User::where('id', $userId)->update(['last_seen_at' => now()]);
    }




    private function recentDeletedMessageIds(ChatConversation $conversation): array
    {
        return ChatMessage::withTrashed()
            ->where('chat_conversation_id', $conversation->id)
            ->whereNotNull('deleted_at')
            ->latest('deleted_at')
            ->limit(100)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    private function recentReadStatuses(ChatConversation $conversation, int $viewerId): array
    {
        return ChatMessage::query()
            ->where('chat_conversation_id', $conversation->id)
            ->where('sender_id', $viewerId)
            ->latest()
            ->limit(30)
            ->get()
            ->map(function ($message) use ($viewerId) {
                return [
                    'id' => $message->id,
                    'read_label' => $this->messageReadLabel($message, $viewerId),
                ];
            })
            ->values()
            ->all();
    }

    private function messageReadLabel(ChatMessage $message, int $viewerId): string
    {
        if ((int) $message->sender_id !== (int) $viewerId) {
            return '';
        }

        $otherReadAt = ChatParticipant::where('chat_conversation_id', $message->chat_conversation_id)
            ->where('user_id', '!=', $viewerId)
            ->max('last_read_at');

        if (! $otherReadAt) {
            return 'Sent';
        }

        return \Carbon\Carbon::parse($otherReadAt)->greaterThanOrEqualTo($message->created_at)
            ? 'Seen'
            : 'Sent';
    }

    private function formatMessagePayload(ChatMessage $message, int $viewerId): array
    {
        $message->loadMissing(['sender', 'attachments']);

        $senderName = $message->sender
            ? ($message->sender->full_name ?: $message->sender->email ?: 'User')
            : 'User';

        return [
            'id' => $message->id,
            'body' => $message->body,
            'time' => optional($message->created_at)->format('h:i A') ?: '',
            'sender_name' => $senderName,
            'is_mine' => (int) $message->sender_id === (int) $viewerId,
            'attachments' => $message->attachments->map(function ($attachment) {
                return [
                    'id' => $attachment->id,
                    'name' => $attachment->original_name,
                    'url' => $attachment->url,
                    'mime_type' => $attachment->mime_type,
                    'size' => $attachment->human_size,
                    'is_image' => $attachment->is_image,
                    'is_video' => $attachment->is_video,
                    'is_pdf' => $attachment->is_pdf,
                ];
            })->values(),
        ];
    }
}