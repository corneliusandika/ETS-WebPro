<?php

namespace Database\Seeders;

use App\Models\Budget;
use App\Models\Goal;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Services\DefaultDataService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'demo@dompetku.test'],
            ['name' => 'Demo User', 'password' => 'password']
        );

        DefaultDataService::provision($user);

        $categories = $user->categories()->get()->keyBy('name');
        $wallets = $user->wallets()->get()->keyBy('name');

        $bank = $wallets['Rekening Bank'];
        $cash = $wallets['Dompet Tunai'];
        $ewallet = $wallets['E-Wallet'];

        $bank->update(['initial_balance' => 2500000]);
        $cash->update(['initial_balance' => 1000000]);
        $ewallet->update(['initial_balance' => 10000000]);

        if ($user->transactions()->exists()) {
            return;
        }

        $expensePlan = [
            'Makan & Minum' => [15000, 75000, ['Makan siang', 'Kopi sore', 'Makan malam', 'Jajan pasar']],
            'Transportasi' => [10000, 60000, ['Ojek online', 'Bensin', 'Parkir', 'Tarif KRL']],
            'Belanja' => [50000, 350000, ['Belanja bulanan', 'Perlengkapan rumah', 'Baju kerja']],
            'Tagihan' => [120000, 450000, ['Listrik & air', 'Internet rumah', 'Pulsa & kuota']],
            'Hiburan' => [40000, 200000, ['Nonton bioskop', 'Langganan streaming', 'Konser mini']],
            'Kesehatan' => [50000, 250000, ['Vitamin', 'Konsultasi dokter', 'Obat apotek']],
            'Tempat Tinggal' => [900000, 1500000, ['Sewa kos', 'Perbaikan atap', 'Iuran kebersihan']],
            'Lainnya' => [20000, 150000, ['Donasi', 'Hadiah ulang tahun', 'Biaya tak terduga']],
        ];

        mt_srand(20261026);

        for ($offset = 5; $offset >= 0; $offset--) {
            $month = now()->startOfMonth()->subMonths($offset);
            $lastDay = $offset === 0 ? now()->day : $month->daysInMonth;
            $isCurrentMonth = $offset === 0;

            if (! $isCurrentMonth || now()->day >= 25) {
                Transaction::create([
                    'user_id' => $user->id,
                    'wallet_id' => $bank->id,
                    'category_id' => $categories['Gaji']->id,
                    'type' => 'income',
                    'amount' => 8500000,
                    'date' => $month->copy()->day(min(25, $lastDay))->toDateString(),
                    'note' => 'Gaji bulanan',
                ]);
            }

            if ($offset % 2 === 0) {
                Transaction::create([
                    'user_id' => $user->id,
                    'wallet_id' => $bank->id,
                    'category_id' => $categories['Freelance']->id,
                    'type' => 'income',
                    'amount' => 1750000,
                    'date' => $month->copy()->day(min(12, $lastDay))->toDateString(),
                    'note' => 'Proyek freelance',
                ]);
            }

            if ($offset % 3 === 0) {
                Transaction::create([
                    'user_id' => $user->id,
                    'wallet_id' => $bank->id,
                    'category_id' => $categories['Bonus']->id,
                    'type' => 'income',
                    'amount' => 750000,
                    'date' => $month->copy()->day(min(8, $lastDay))->toDateString(),
                    'note' => 'Bonus kinerja',
                ]);
            }

            $count = $isCurrentMonth ? (int) ceil($lastDay / 2) : 18;

            for ($i = 0; $i < $count; $i++) {
                $name = array_rand($expensePlan);
                [$min, $max, $notes] = $expensePlan[$name];

                $amount = mt_rand($min, $max);
                $amount = (int) (round($amount / 500) * 500);

                $day = mt_rand(1, $lastDay);
                $daysAgo = $isCurrentMonth ? $lastDay - $day : $month->daysInMonth - $day;

                if ($isCurrentMonth && $daysAgo < 0) {
                    $day = $lastDay;
                }

                $wallet = match ($name) {
                    'Tagihan', 'Tempat Tinggal' => $bank,
                    'Transportasi', 'Makan & Minum' => mt_rand(0, 1) ? $cash : $ewallet,
                    default => $ewallet,
                };

                Transaction::create([
                    'user_id' => $user->id,
                    'wallet_id' => $wallet->id,
                    'category_id' => $categories[$name]->id,
                    'type' => 'expense',
                    'amount' => $amount,
                    'date' => $month->copy()->day($day)->toDateString(),
                    'note' => $notes[array_rand($notes)],
                ]);
            }
        }

        $budgetPlan = [
            'Makan & Minum' => 1500000,
            'Transportasi' => 600000,
            'Belanja' => 1000000,
            'Tagihan' => 800000,
            'Hiburan' => 400000,
        ];

        foreach ($budgetPlan as $name => $amount) {
            Budget::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'category_id' => $categories[$name]->id,
                    'month' => now()->startOfMonth()->toDateString(),
                ],
                ['amount' => $amount]
            );
        }

        $goalPlan = [
            ['name' => 'Dana Darurat', 'target_amount' => 20000000, 'saved_amount' => 7500000, 'color' => '#10b981'],
            ['name' => 'Liburan Bali', 'target_amount' => 6000000, 'saved_amount' => 2250000, 'color' => '#8b5cf6'],
            ['name' => 'Laptop Baru', 'target_amount' => 15000000, 'saved_amount' => 3000000, 'color' => '#0ea5e9'],
        ];

        foreach ($goalPlan as $goal) {
            Goal::firstOrCreate(
                ['user_id' => $user->id, 'name' => $goal['name']],
                $goal + ['target_date' => now()->addMonths(6)->toDateString()]
            );
        }

        Wallet::query()->where('user_id', $user->id)->where('name', 'Dompet Tunai')->update(['type' => 'cash']);
    }
}
