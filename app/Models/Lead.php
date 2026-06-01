<?php

namespace App\Models;

use App\Enums\LeadStage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lead extends Model
{
    protected $fillable = [
        'name', 'email', 'phone', 'company', 'source', 'deal_value',
        'stage', 'notes', 'assigned_to', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'stage'      => LeadStage::class,
            'deal_value' => 'decimal:2',
        ];
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
