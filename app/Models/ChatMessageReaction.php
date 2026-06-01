<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMessageReaction extends Model
{
    public $timestamps = false;

    protected $fillable = ['message_id', 'user_id', 'emoji'];

    public $table = 'chat_message_reactions';

    protected $casts = ['created_at' => 'datetime'];

    public function message(): BelongsTo
    {
        return $this->belongsTo(ChatMessage::class, 'message_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
