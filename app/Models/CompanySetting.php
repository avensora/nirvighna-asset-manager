<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get(string $key, mixed $default = null): mixed
    {
        return static::where('key', $key)->value('value') ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    public static function currentBalance(): float
    {
        $opening     = (float) static::get('opening_balance', 0);
        $openingDate = static::get('opening_balance_date', '1970-01-01');

        $income  = (float) Transaction::where('type', 'income') ->where('date', '>=', $openingDate)->sum('amount');
        $expense = (float) Transaction::where('type', 'expense')->where('date', '>=', $openingDate)->sum('amount');

        return $opening + $income - $expense;
    }
}
