<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TwoFactorConfig extends Model
{
    protected $fillable = ['user_id', 'secret', 'recovery_codes', 'confirmed_at'];

    protected $casts = [
        'recovery_codes' => 'array',
        'confirmed_at'   => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isConfirmed(): bool
    {
        return $this->confirmed_at !== null;
    }
}
