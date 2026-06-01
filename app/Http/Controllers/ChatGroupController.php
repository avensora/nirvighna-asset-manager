<?php

namespace App\Http\Controllers;

use App\Models\ChatGroup;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ChatGroupController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'member_ids'  => 'nullable|array',
            'member_ids.*' => 'exists:users,id',
        ]);

        $group = ChatGroup::create([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'created_by'  => auth()->id(),
        ]);

        // Always add creator as a member
        $memberIds = collect($data['member_ids'] ?? [])->push(auth()->id())->unique()->values();
        foreach ($memberIds as $userId) {
            $group->members()->attach($userId, ['joined_at' => now()]);
        }

        return redirect()->route('chat-groups.show', $group)
            ->with('success', "Group \"{$group->name}\" created.");
    }

    public function show(ChatGroup $group): View
    {
        $user = auth()->user();

        if (! $user->isManager() && ! $group->hasMember($user->id)) {
            abort(403, 'You are not a member of this group.');
        }

        $messages = ChatMessage::with(['user', 'reactions'])
            ->where('group_id', $group->id)
            ->latest()
            ->take(50)
            ->get()
            ->reverse()
            ->values()
            ->map(fn (ChatMessage $m) => $this->formatMessage($m));

        $groups = $this->getUserGroups();

        return view('chat.index', compact('messages', 'group', 'groups'));
    }

    public function addMember(Request $request, ChatGroup $group): RedirectResponse
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $group->members()->syncWithoutDetaching([$data['user_id'] => ['joined_at' => now()]]);

        return back()->with('success', 'Member added.');
    }

    public function removeMember(ChatGroup $group, User $user): RedirectResponse
    {
        $group->members()->detach($user->id);

        return back()->with('success', 'Member removed.');
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
