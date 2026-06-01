<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    public function index()
    {
        $messages = ChatMessage::with('user')
            ->latest()
            ->take(50)
            ->get()
            ->reverse()
            ->values()
            ->map(fn (ChatMessage $m) => $this->formatMessage($m));

        return view('chat.index', ['messages' => $messages]);
    }

    public function messages(Request $request)
    {
        $since = (int) $request->query('since', 0);

        $messages = ChatMessage::with('user')
            ->where('id', '>', $since)
            ->orderBy('id')
            ->get()
            ->map(fn (ChatMessage $m) => $this->formatMessage($m));

        return response()->json($messages);
    }

    public function store(Request $request)
    {
        $request->validate([
            'body'       => 'nullable|string|max:5000',
            'attachment' => 'nullable|file|max:5120|mimes:jpeg,jpg,png,gif,pdf,doc,docx,xls,xlsx,zip',
        ]);

        if (!$request->filled('body') && !$request->hasFile('attachment')) {
            return response()->json(['error' => 'Message or attachment required.'], 422);
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
            'body'            => $request->input('body'),
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
        ]);

        $message->load('user');

        return response()->json($this->formatMessage($message));
    }

    public function attachment(ChatMessage $message)
    {
        if (!$message->attachment_path) {
            abort(404);
        }

        $fullPath = Storage::disk('local')->path($message->attachment_path);

        if (!file_exists($fullPath)) {
            abort(404);
        }

        return response()->file($fullPath, [
            'Content-Disposition' => 'inline; filename="' . $message->attachment_name . '"',
        ]);
    }

    private function formatMessage(ChatMessage $message): array
    {
        $isImage = $message->attachment_name &&
            Str::endsWith(strtolower($message->attachment_name), ['jpg', 'jpeg', 'png', 'gif']);

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
        ];
    }
}
