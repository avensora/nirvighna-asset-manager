<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'invoice_number',
        'client_id',
        'project_id',
        'status',
        'issue_date',
        'due_date',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'discount_amount',
        'total',
        'notes',
        'void_reason',
        'voided_at',
        'voided_by',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'status'          => InvoiceStatus::class,
            'issue_date'      => 'date',
            'due_date'        => 'date',
            'subtotal'        => 'decimal:2',
            'tax_rate'        => 'decimal:2',
            'tax_amount'      => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total'           => 'decimal:2',
            'voided_at'       => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(InvoicePayment::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function amountPaid(): float
    {
        return (float) $this->payments()->sum('amount');
    }

    public function amountDue(): float
    {
        return max(0, (float) $this->total - $this->amountPaid());
    }

    public function isOverdue(): bool
    {
        return in_array($this->status, [InvoiceStatus::Sent, InvoiceStatus::Partial])
            && $this->due_date !== null
            && $this->due_date->isPast();
    }

    public static function generateNumber(): string
    {
        $year   = now()->year;
        $prefix = "INV-{$year}-";
        $max    = static::where('invoice_number', 'like', $prefix . '%')
            ->lockForUpdate()
            ->max('invoice_number');
        $next   = $max ? ((int) substr($max, \strlen($prefix))) + 1 : 1;
        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}
