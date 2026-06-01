<?php

namespace App\Models;

use App\Enums\LoanSourceType;
use App\Enums\LoanStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Loan extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'source_name',
        'source_type',
        'principal_amount',
        'borrowed_date',
        'due_date',
        'purpose',
        'notes',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'source_type'      => LoanSourceType::class,
            'status'           => LoanStatus::class,
            'principal_amount' => 'decimal:2',
            'borrowed_date'    => 'date',
            'due_date'         => 'date',
        ];
    }

    public function repayments(): HasMany
    {
        return $this->hasMany(LoanRepayment::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function amountRepaid(): float
    {
        return (float) $this->repayments()->sum('amount');
    }

    public function amountOutstanding(): float
    {
        return max(0, (float) $this->principal_amount - $this->amountRepaid());
    }

    public function syncStatus(): void
    {
        $repaid = $this->amountRepaid();
        $principal = (float) $this->principal_amount;

        if ($repaid <= 0) {
            $status = LoanStatus::Outstanding;
        } elseif ($repaid >= $principal) {
            $status = LoanStatus::Repaid;
        } else {
            $status = LoanStatus::PartiallyRepaid;
        }

        $this->update(['status' => $status]);
    }
}
