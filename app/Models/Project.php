<?php

namespace App\Models;

use App\Enums\ProjectPriority;
use App\Enums\ProjectStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $fillable = [
        'title',
        'description',
        'client_id',
        'status',
        'priority',
        'start_date',
        'deadline',
        'budget',
        'progress',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'status'     => ProjectStatus::class,
            'priority'   => ProjectPriority::class,
            'start_date' => 'date',
            'deadline'   => 'date',
            'budget'     => 'decimal:2',
            'progress'   => 'integer',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ProjectAssignment::class);
    }

    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_assignments')
                    ->withPivot('role', 'assigned_at')
                    ->withTimestamps();
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function calendarEvents(): HasMany
    {
        return $this->hasMany(CalendarEvent::class);
    }

    public function isAssigned(int $userId): bool
    {
        return $this->assignments()->where('user_id', $userId)->exists();
    }

    public function isOverdue(): bool
    {
        return $this->deadline
            && $this->deadline->isPast()
            && ! in_array($this->status, [ProjectStatus::Completed, ProjectStatus::Cancelled]);
    }
}
