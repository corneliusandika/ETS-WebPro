<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesPeriod;
use App\Models\Budget;
use App\Models\Goal;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    use ResolvesPeriod;

    public function __invoke(Request $request): View
    {
        $month = $this->resolveMonth($request->query('month'));
        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();

        $monthTransactions = Transaction::with(['category', 'wallet'])
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get();

        $income = (float) $monthTransactions->where('type', 'income')->sum('amount');
        $expense = (float) $monthTransactions->where('type', 'expense')->sum('amount');

        $wallets = Wallet::query()
            ->withSum(['transactions as income_total' => fn ($query) => $query->where('type', 'income')], 'amount')
            ->withSum(['transactions as expense_total' => fn ($query) => $query->where('type', 'expense')], 'amount')
            ->orderBy('name')
            ->get();

        $totalBalance = $wallets->sum(fn (Wallet $wallet) => $wallet->balance);

        $chart = collect(range(5, 0))->map(function (int $offset) use ($start) {
            $month = $start->copy()->subMonths($offset);

            $transactions = Transaction::query()
                ->whereYear('date', $month->year)
                ->whereMonth('date', $month->month)
                ->get(['type', 'amount']);

            return [
                'label' => nama_bulan($month, false).' '.substr((string) $month->year, 2),
                'income' => (float) $transactions->where('type', 'income')->sum('amount'),
                'expense' => (float) $transactions->where('type', 'expense')->sum('amount'),
            ];
        });

        $breakdown = $monthTransactions
            ->where('type', 'expense')
            ->groupBy('category_id')
            ->map(function ($items) {
                $category = $items->first()->category;

                return [
                    'name' => $category->name ?? 'Tanpa kategori',
                    'color' => $category->color ?? '#94a3b8',
                    'icon' => $category->icon ?? '📦',
                    'total' => (float) $items->sum('amount'),
                ];
            })
            ->sortByDesc('total')
            ->take(5)
            ->values();

        $budgets = Budget::with('category')
            ->whereYear('month', $start->year)
            ->whereMonth('month', $start->month)
            ->orderByDesc('amount')
            ->get();

        $goals = Goal::query()
            ->orderByDesc('saved_amount')
            ->take(3)
            ->get();

        $recentTransactions = Transaction::with(['category', 'wallet'])
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->take(6)
            ->get();

        $daysInMonth = $start->daysInMonth;
        $dailyAverage = $expense > 0 ? $expense / max(1, $start->isCurrentMonth() ? now()->day : $daysInMonth) : 0;

        return view('dashboard', [
            'month' => $month,
            'monthOptions' => $this->monthOptions(),
            'income' => $income,
            'expense' => $expense,
            'totalBalance' => $totalBalance,
            'wallets' => $wallets,
            'chart' => $chart,
            'breakdown' => $breakdown,
            'budgets' => $budgets,
            'goals' => $goals,
            'recentTransactions' => $recentTransactions,
            'dailyAverage' => $dailyAverage,
            'transactionCount' => $monthTransactions->count(),
        ]);
    }
}
