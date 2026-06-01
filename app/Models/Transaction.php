<?php

namespace App\Models;

use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = [
        'type',
        'category',
        'amount',
        'date',
        'description',
        'reference',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'type'   => TransactionType::class,
            'amount' => 'decimal:2',
            'date'   => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
