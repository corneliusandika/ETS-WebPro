<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'name', 'type', 'initial_balance', 'color', 'is_active'])]
class Wallet extends Model
{
    use BelongsToUser, HasFactory;

    public const TYPES = [
        'cash' => 'Tunai',
        'bank' => 'Bank',
        'ewallet' => 'E-Wallet',
    ];

    public const COLORS = [
        '#0ea5e9', '#6366f1', '#8b5cf6', '#10b981', '#f59e0b', '#ef4444', '#64748b',
    ];

    protected function casts(): array
    {
        return [
            'initial_balance' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function getBalanceAttribute(): float
    {
        if (array_key_exists('income_total', $this->attributes) || array_key_exists('expense_total', $this->attributes)) {
            $income = (float) ($this->attributes['income_total'] ?? 0);
            $expense = (float) ($this->attributes['expense_total'] ?? 0);
        } else {
            $totals = $this->transactions()
                ->selectRaw("COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) as income_total")
                ->selectRaw("COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) as expense_total")
                ->first();

            $income = (float) ($totals->income_total ?? 0);
            $expense = (float) ($totals->expense_total ?? 0);
        }

        return (float) $this->initial_balance + $income - $expense;
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? ucfirst($this->type);
    }
}
