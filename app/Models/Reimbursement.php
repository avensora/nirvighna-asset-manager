<?php

namespace App\Models;

use App\Enums\ReimbursementStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reimbursement extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'amount',
        'spent_date',
        'category',
        'description',
        'status',
        'approved_by',
        'approved_at',
        'reimbursed_by',
        'reimbursed_at',
        'rejection_reason',
        'project_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount'        => 'decimal:2',
            'spent_date'    => 'date',
            'status'        => ReimbursementStatus::class,
            'approved_at'   => 'datetime',
            'reimbursed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function reimbursedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reimbursed_by');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
