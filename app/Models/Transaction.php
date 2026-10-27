<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'wallet_id', 'category_id', 'type', 'amount', 'date', 'note'])]
class Transaction extends Model
{
    use BelongsToUser, HasFactory;

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'date' => 'date',
        ];
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function scopeOfType(Builder $query, ?string $type): Builder
    {
        return $query->when(
            in_array($type, ['income', 'expense'], true),
            fn (Builder $q) => $q->where('type', $type)
        );
    }

    public function scopeInMonth(Builder $query, ?string $month): Builder
    {
        if (! $month || ! preg_match('/^\d{4}-\d{2}$/', $month)) {
            return $query;
        }

        return $query->whereYear('date', substr($month, 0, 4))
            ->whereMonth('date', substr($month, 5, 2));
    }

    public function getIsIncomeAttribute(): bool
    {
        return $this->type === 'income';
    }
}
