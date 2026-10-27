<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Membatasi setiap query ke data milik user yang sedang login,
 * sekaligus mengisi user_id otomatis saat membuat data baru.
 */
trait BelongsToUser
{
    protected static function bootBelongsToUser(): void
    {
        static::addGlobalScope('owner', function (Builder $builder) {
            if (auth()->hasUser()) {
                $builder->where($builder->getModel()->getTable().'.user_id', auth()->id());
            }
        });

        static::creating(function ($model) {
            if (auth()->hasUser() && $model->user_id === null) {
                $model->user_id = auth()->id();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
