<?php

namespace App\Services;

use App\Models\Category;
use App\Models\User;
use App\Models\Wallet;

class DefaultDataService
{
    public const CATEGORIES = [
        ['name' => 'Gaji', 'type' => 'income', 'color' => '#22c55e', 'icon' => '💼'],
        ['name' => 'Freelance', 'type' => 'income', 'color' => '#14b8a6', 'icon' => '💻'],
        ['name' => 'Bonus', 'type' => 'income', 'color' => '#0ea5e9', 'icon' => '🎁'],
        ['name' => 'Investasi', 'type' => 'income', 'color' => '#6366f1', 'icon' => '📈'],
        ['name' => 'Makan & Minum', 'type' => 'expense', 'color' => '#f97316', 'icon' => '🍔'],
        ['name' => 'Transportasi', 'type' => 'expense', 'color' => '#0ea5e9', 'icon' => '🚗'],
        ['name' => 'Tempat Tinggal', 'type' => 'expense', 'color' => '#8b5cf6', 'icon' => '🏠'],
        ['name' => 'Belanja', 'type' => 'expense', 'color' => '#ec4899', 'icon' => '🛍️'],
        ['name' => 'Tagihan', 'type' => 'expense', 'color' => '#eab308', 'icon' => '💡'],
        ['name' => 'Hiburan', 'type' => 'expense', 'color' => '#f43f5e', 'icon' => '🎬'],
        ['name' => 'Kesehatan', 'type' => 'expense', 'color' => '#10b981', 'icon' => '🏥'],
        ['name' => 'Lainnya', 'type' => 'expense', 'color' => '#64748b', 'icon' => '📦'],
    ];

    public const WALLETS = [
        ['name' => 'Dompet Tunai', 'type' => 'cash', 'color' => '#0ea5e9', 'initial_balance' => 0],
        ['name' => 'Rekening Bank', 'type' => 'bank', 'color' => '#6366f1', 'initial_balance' => 0],
        ['name' => 'E-Wallet', 'type' => 'ewallet', 'color' => '#8b5cf6', 'initial_balance' => 0],
    ];

    public static function provision(User $user): void
    {
        foreach (self::CATEGORIES as $category) {
            Category::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'name' => $category['name'],
                    'type' => $category['type'],
                ],
                [
                    'color' => $category['color'],
                    'icon' => $category['icon'],
                ]
            );
        }

        foreach (self::WALLETS as $wallet) {
            Wallet::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'name' => $wallet['name'],
                ],
                [
                    'type' => $wallet['type'],
                    'color' => $wallet['color'],
                    'initial_balance' => $wallet['initial_balance'],
                ]
            );
        }
    }
}
