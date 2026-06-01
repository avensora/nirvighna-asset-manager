<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonthClosing extends Model
{
    protected $fillable = [
        'year',
        'month',
        'closed_by',
        'closed_at',
        'opening_balance',
        'closing_balance',
        'total_income',
        'total_expenses',
    ];

    protected function casts(): array
    {
        return [
            'closed_at'       => 'datetime',
            'opening_balance' => 'decimal:2',
            'closing_balance' => 'decimal:2',
            'total_income'    => 'decimal:2',
            'total_expenses'  => 'decimal:2',
        ];
    }

    public function closer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public static function isClosed(int $year, int $month): bool
    {
        return static::where('year', $year)->where('month', $month)->exists();
    }

    public function monthLabel(): string
    {
        return \Carbon\Carbon::createFromDate($this->year, $this->month, 1)->format('F Y');
    }
}
