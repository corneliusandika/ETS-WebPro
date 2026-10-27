<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'name', 'target_amount', 'saved_amount', 'target_date', 'color'])]
class Goal extends Model
{
    use BelongsToUser, HasFactory;

    protected function casts(): array
    {
        return [
            'target_amount' => 'decimal:2',
            'saved_amount' => 'decimal:2',
            'target_date' => 'date',
        ];
    }

    public function getProgressAttribute(): float
    {
        if ((float) $this->target_amount <= 0) {
            return 0.0;
        }

        return round(min(100, (float) $this->saved_amount / (float) $this->target_amount * 100), 1);
    }

    public function getRemainingAttribute(): float
    {
        return max(0, (float) $this->target_amount - (float) $this->saved_amount);
    }

    public function getIsCompletedAttribute(): bool
    {
        return (float) $this->saved_amount >= (float) $this->target_amount;
    }
}
