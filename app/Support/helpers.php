<?php

use Carbon\Carbon;
use Illuminate\Support\Carbon as IlluminateCarbon;

if (! function_exists('rupiah')) {
    function rupiah(float|int|string|null $amount, bool $withPrefix = true): string
    {
        $value = (float) $amount;
        $formatted = number_format(abs($value), 0, ',', '.');
        $sign = $value < 0 ? '-' : '';

        return ($withPrefix ? $sign.'Rp ' : $sign).$formatted;
    }
}

if (! function_exists('rupiah_short')) {
    function rupiah_short(float|int|string|null $amount): string
    {
        $value = (float) $amount;
        $sign = $value < 0 ? '-' : '';
        $value = abs($value);

        if ($value >= 1_000_000_000) {
            return $sign.'Rp '.rtrim(rtrim(number_format($value / 1_000_000_000, 1, ',', '.'), '0'), ',').' M';
        }

        if ($value >= 1_000_000) {
            return $sign.'Rp '.rtrim(rtrim(number_format($value / 1_000_000, 1, ',', '.'), '0'), ',').' jt';
        }

        if ($value >= 1_000) {
            return $sign.'Rp '.rtrim(rtrim(number_format($value / 1_000, 1, ',', '.'), '0'), ',').' rb';
        }

        return $sign.'Rp '.number_format($value, 0, ',', '.');
    }
}

if (! function_exists('nama_bulan')) {
    /**
     * @param  Carbon|IlluminateCarbon|string|null  $date
     */
    function nama_bulan($date = null, bool $withYear = true): string
    {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $carbon = $date instanceof Carbon || $date instanceof IlluminateCarbon
            ? $date
            : ($date ? Carbon::parse($date) : now());

        $label = $months[(int) $carbon->month];

        return $withYear ? $label.' '.$carbon->year : $label;
    }
}

if (! function_exists('tanggal_indonesia')) {
    /**
     * @param  Carbon|IlluminateCarbon|string|null  $date
     */
    function tanggal_indonesia($date = null, bool $withYear = true): string
    {
        $carbon = $date instanceof Carbon || $date instanceof IlluminateCarbon
            ? $date
            : ($date ? Carbon::parse($date) : now());

        $shortMonths = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
            7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];

        $label = $carbon->day.' '.$shortMonths[(int) $carbon->month];

        return $withYear ? $label.' '.$carbon->year : $label;
    }
}
