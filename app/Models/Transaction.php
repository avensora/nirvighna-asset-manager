<?php

namespace App\Models;

use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'type',
        'category',
        'amount',
        'date',
        'description',
        'reference',
        'user_id',
        'project_id',
        'approval_status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'void_reason',
    ];

    protected function casts(): array
    {
        return [
            'type'        => TransactionType::class,
            'amount'      => 'decimal:2',
            'date'        => 'date',
            'approved_at' => 'datetime',
        ];
    }

    public function isApproved(): bool
    {
        return $this->approval_status === 'approved';
    }

    public function isPending(): bool
    {
        return $this->approval_status === 'pending';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
