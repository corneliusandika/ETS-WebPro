<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'category_id', 'month', 'amount'])]
class Budget extends Model
{
    use BelongsToUser, HasFactory;

    protected function casts(): array
    {
        return [
            'month' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function spent(): float
    {
        if (! $this->category_id || ! $this->month) {
            return 0.0;
        }

        return (float) Transaction::query()
            ->where('category_id', $this->category_id)
            ->where('type', 'expense')
            ->whereBetween('date', [
                $this->month->copy()->startOfMonth()->toDateString(),
                $this->month->copy()->endOfMonth()->toDateString(),
            ])
            ->sum('amount');
    }

    public function getPercentAttribute(): float
    {
        if ((float) $this->amount <= 0) {
            return 0.0;
        }

        return round($this->spent() / (float) $this->amount * 100, 1);
    }

    public function getStatusAttribute(): string
    {
        return match (true) {
            $this->percent >= 100 => 'over',
            $this->percent >= 80 => 'warning',
            default => 'safe',
        };
    }
}
