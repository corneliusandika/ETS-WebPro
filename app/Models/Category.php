<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'name', 'type', 'color', 'icon'])]
class Category extends Model
{
    use BelongsToUser, HasFactory;

    public const ICONS = [
        '💼', '💻', '🎁', '📈', '🏦', '🛒',
        '🍔', '🚗', '🏠', '🛍️', '💡', '🎬',
        '🏥', '📚', '✈️', '☕', '🐾', '📦',
    ];

    public const COLORS = [
        '#6366f1', '#8b5cf6', '#ec4899', '#f43f5e', '#f97316', '#eab308',
        '#22c55e', '#10b981', '#14b8a6', '#06b6d4', '#0ea5e9', '#64748b',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class);
    }

    public function scopeExpense($query)
    {
        return $query->where('type', 'expense');
    }

    public function scopeIncome($query)
    {
        return $query->where('type', 'income');
    }
}
