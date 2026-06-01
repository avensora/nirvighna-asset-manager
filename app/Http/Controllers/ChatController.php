<?php

namespace App\Http\Controllers;

use App\Models\ChatGroup;
use App\Models\ChatMessage;
use App\Models\ChatMessageReaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    public function index()
    {
        $messages = ChatMessage::with(['user', 'reactions'])
            ->whereNull('group_id')
            ->latest()
            ->take(50)
            ->get()
            ->reverse()
            ->values()
            ->map(fn (ChatMessage $m) => $this->formatMessage($m));

        $groups = $this->getUserGroups();

        return view('chat.index', ['messages' => $messages, 'group' => null, 'groups' => $groups]);
    }

    public function messages(Request $request): JsonResponse
    {
        $since   = (int) $request->query('since', 0);
        $groupId = $request->query('group_id') ? (int) $request->query('group_id') : null;

        // Gate: team leads can only poll groups they're in
        if ($groupId) {
            $group = ChatGroup::findOrFail($groupId);
            $user  = auth()->user();
            if (! $user->isManager() && ! $group->hasMember($user->id)) {
                return response()->json(['error' => 'Not a member'], 403);
            }
        }

        $messages = ChatMessage::with(['user', 'reactions'])
            ->where('group_id', $groupId)
            ->where('id', '>', $since)
            ->orderBy('id')
            ->get()
            ->map(fn (ChatMessage $m) => $this->formatMessage($m));

        // Update read cursor
        if ($messages->isNotEmpty()) {
            $maxId         = $messages->max('id');
            $cursorGroupId = $groupId ?? 0;
            DB::table('chat_read_cursors')->upsert(
                ['user_id' => auth()->id(), 'group_id' => $cursorGroupId, 'last_read_message_id' => $maxId],
                ['user_id', 'group_id'],
                ['last_read_message_id']
            );
        }

        // Collect typists (exclude self, filter stale entries)
        $roomKey  = 'typing.' . ($groupId ?? 'main');
        $typists  = Cache::get($roomKey, []);
        $cutoff   = now()->timestamp - 4;
        $typistNames = [];
        foreach ($typists as $uid => $data) {
            if ((int) $uid !== auth()->id() && ($data['at'] ?? 0) > $cutoff) {
                $typistNames[] = $data['name'];
            }
        }

        return response()->json([
            'messages' => $messages,
            'typists'  => $typistNames,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'body'       => 'nullable|string|max:5000',
            'attachment' => 'nullable|file|max:5120|mimes:jpeg,jpg,png,gif,pdf,doc,docx,xls,xlsx,zip',
            'group_id'   => 'nullable|integer|exists:chat_groups,id',
        ]);

        if (! $request->filled('body') && ! $request->hasFile('attachment')) {
            return response()->json(['error' => 'Message or attachment required.'], 422);
        }

        $groupId = $request->input('group_id') ? (int) $request->input('group_id') : null;

        // Gate membership for group messages
        if ($groupId) {
            $group = ChatGroup::findOrFail($groupId);
            $user  = auth()->user();
            if (! $user->isManager() && ! $group->hasMember($user->id)) {
                return response()->json(['error' => 'Not a member of this group.'], 403);
            }
        }

        $attachmentPath = null;
        $attachmentName = null;

        if ($request->hasFile('attachment')) {
            $file           = $request->file('attachment');
            $attachmentName = $file->getClientOriginalName();
            $attachmentPath = $file->store('chat-attachments', 'local');
        }

        $message = ChatMessage::create([
            'user_id'         => auth()->id(),
            'group_id'        => $groupId,
            'body'            => $request->input('body'),
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
        ]);

        $message->load(['user', 'reactions']);

        // Clear typing indicator for this user
        $roomKey = 'typing.' . ($groupId ?? 'main');
        $typists = Cache::get($roomKey, []);
        unset($typists[auth()->id()]);
        Cache::put($roomKey, $typists, 60);

        return response()->json($this->formatMessage($message));
    }

    public function typing(Request $request): JsonResponse
    {
        $groupId = $request->input('group_id') ? (int) $request->input('group_id') : null;
        $roomKey = 'typing.' . ($groupId ?? 'main');
        $typists = Cache::get($roomKey, []);
        $typists[auth()->id()] = ['name' => auth()->user()->name, 'at' => now()->timestamp];
        Cache::put($roomKey, $typists, 60);

        return response()->json(['ok' => true]);
    }

    public function react(Request $request, ChatMessage $message): JsonResponse
    {
        $data  = $request->validate(['emoji' => 'required|string|in:👍,❤️,😂,😮,😢']);
        $emoji = $data['emoji'];

        $existing = ChatMessageReaction::where('message_id', $message->id)
            ->where('user_id', auth()->id())
            ->where('emoji', $emoji)
            ->first();

        if ($existing) {
            $existing->delete();
        } else {
            ChatMessageReaction::create([
                'message_id' => $message->id,
                'user_id'    => auth()->id(),
                'emoji'      => $emoji,
            ]);
        }

        $message->load('reactions');
        $reactions = $message->reactions->groupBy('emoji')->map(function ($items, $emoji) {
            return [
                'emoji'   => $emoji,
                'count'   => $items->count(),
                'reacted' => $items->contains('user_id', auth()->id()),
            ];
        })->values()->all();

        return response()->json(['message_id' => $message->id, 'reactions' => $reactions]);
    }

    public function attachment(ChatMessage $message)
    {
        if (! $message->attachment_path) {
            abort(404);
        }

        $fullPath = Storage::disk('local')->path($message->attachment_path);

        if (! file_exists($fullPath)) {
            abort(404);
        }

        return response()->file($fullPath, [
            'Content-Disposition' => 'inline; filename="' . $message->attachment_name . '"',
        ]);
    }

    private function getUserGroups(): \Illuminate\Database\Eloquent\Collection
    {
        $user = auth()->user();

        if ($user->isManager()) {
            return ChatGroup::orderBy('name')->get();
        }

        return ChatGroup::whereHas('members', fn ($q) => $q->where('user_id', $user->id))
            ->orderBy('name')
            ->get();
    }

    private function formatMessage(ChatMessage $message): array
    {
        $isImage = $message->attachment_name &&
            Str::endsWith(strtolower($message->attachment_name), ['jpg', 'jpeg', 'png', 'gif']);

        $reactions = $message->reactions->groupBy('emoji')->map(function ($items, $emoji) {
            return [
                'emoji'   => $emoji,
                'count'   => $items->count(),
                'reacted' => $items->contains('user_id', auth()->id()),
            ];
        })->values()->all();

        return [
            'id'              => $message->id,
            'user_id'         => $message->user_id,
            'user_name'       => $message->user->name,
            'body'            => $message->body,
            'attachment_name' => $message->attachment_name,
            'attachment_url'  => $message->attachment_path
                                    ? route('chat.attachment', $message->id)
                                    : null,
            'is_image'        => $isImage,
            'is_own'          => $message->user_id === auth()->id(),
            'created_at'      => $message->created_at->format('d M, g:i A'),
            'reactions'       => $reactions,
        ];
    }
}
