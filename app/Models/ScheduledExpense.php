<?php

namespace App\Models;

use App\Enums\RecurrenceType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduledExpense extends Model
{
    protected $fillable = [
        'title',
        'amount',
        'category',
        'due_date',
        'recurrence',
        'notes',
        'status',
        'last_paid_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount'       => 'decimal:2',
            'due_date'     => 'date',
            'last_paid_at' => 'date',
            'recurrence'   => RecurrenceType::class,
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isOverdue(): bool
    {
        return $this->status === 'pending' && $this->due_date->isPast();
    }

    public function isDueSoon(): bool
    {
        return $this->status === 'pending'
            && ! $this->due_date->isPast()
            && $this->due_date->diffInDays(today()) <= 7;
    }
}
