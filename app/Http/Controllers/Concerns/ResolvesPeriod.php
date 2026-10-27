<?php

namespace App\Http\Controllers\Concerns;

use Carbon\Carbon;

trait ResolvesPeriod
{
    protected function resolveMonth(?string $value): Carbon
    {
        if ($value && preg_match('/^\d{4}-\d{2}$/', $value)) {
            return Carbon::createFromFormat('Y-m-d', $value.'-01')->startOfMonth();
        }

        return now()->startOfMonth();
    }

    protected function monthOptions(int $back = 11): array
    {
        $options = [];

        for ($i = 0; $i < $back; $i++) {
            $month = now()->startOfMonth()->subMonths($i);
            $options[$month->format('Y-m')] = nama_bulan($month);
        }

        return $options;
    }
}
