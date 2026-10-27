@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    @php
        $hour = (int) now()->format('H');
        $greeting = $hour < 11 ? 'Selamat pagi' : ($hour < 15 ? 'Selamat siang' : ($hour < 18 ? 'Selamat sore' : 'Selamat malam'));
        $net = $income - $expense;
        $breakdownTotal = $breakdown->sum('total');
        $overBudgets = $budgets->filter(fn ($budget) => $budget->percent >= 80);
    @endphp

    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ $greeting }}, {{ auth()->user()->name }}</p>
            <h1 class="mt-1 title-page">Ringkasan {{ nama_bulan($month) }}</h1>
        </div>

        <form method="GET" action="{{ route('dashboard') }}" class="flex items-center gap-2">
            <select name="month" onchange="this.form.submit()" class="input w-auto">
                @foreach ($monthOptions as $value => $label)
                    <option value="{{ $value }}" @selected($month->format('Y-m') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <a href="{{ route('reports.index', ['month' => $month->format('Y-m')]) }}" class="btn btn-secondary">
                @include('partials.icon', ['name' => 'report', 'class' => 'h-4 w-4'])
                Laporan
            </a>
        </form>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-950 via-blue-900 to-sky-950 p-5 text-white shadow-sm ring-1 ring-cyan-400/30 sm:p-6">
            <div class="absolute -right-10 -top-10 h-32 w-32 rounded-full bg-blue-500/20 blur-2xl"></div>
            <div class="relative">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-semibold uppercase tracking-widest text-cyan-300">Total saldo</p>
                    @include('partials.icon', ['name' => 'wallet', 'class' => 'h-5 w-5 text-blue-300'])
                </div>
                <p class="mt-3 text-xl font-bold tracking-tight whitespace-nowrap 2xl:text-2xl">{{ rupiah($totalBalance) }}</p>
                <p class="mt-1 text-xs text-slate-400">Tersebar di <span class="font-semibold text-slate-200">{{ $wallets->count() }}</span> dompet</p>
            </div>
        </div>

        <div class="card card-body">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">Pemasukan</p>
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-400">
                    @include('partials.icon', ['name' => 'arrow-down', 'class' => 'h-5 w-5'])
                </span>
            </div>
            <p class="mt-3 text-xl font-bold tracking-tight whitespace-nowrap 2xl:text-2xl">{{ rupiah($income) }}</p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400"><span class="font-semibold text-slate-700 dark:text-slate-200">{{ $transactionCount }}</span> transaksi bulan ini</p>
        </div>

        <div class="card card-body">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">Pengeluaran</p>
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-rose-50 text-rose-700 dark:bg-rose-950/50 dark:text-rose-400">
                    @include('partials.icon', ['name' => 'arrow-up', 'class' => 'h-5 w-5'])
                </span>
            </div>
            <p class="mt-3 text-xl font-bold tracking-tight whitespace-nowrap 2xl:text-2xl">{{ rupiah($expense) }}</p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Rata-rata <span class="font-semibold text-slate-700 dark:text-slate-200">{{ rupiah($dailyAverage) }}</span> / hari</p>
        </div>

        <div class="card card-body">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold uppercase tracking-widest text-cyan-700 dark:text-cyan-400">Selisih bulan ini</p>
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400">
                    @include('partials.icon', ['name' => 'cash', 'class' => 'h-5 w-5'])
                </span>
            </div>
            <p class="mt-3 text-xl font-bold tracking-tight whitespace-nowrap 2xl:text-2xl {{ $net >= 0 ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-700 dark:text-rose-400' }}">
                {{ $net >= 0 ? '+' : '' }}{{ rupiah($net) }}
            </p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $net >= 0 ? 'Kamu masih surplus' : 'Pengeluaran melebihi pemasukan' }}</p>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="card xl:col-span-2">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 dark:border-slate-700 sm:px-6">
                <div>
                    <h2 class="text-base font-semibold text-slate-900 dark:text-white">Arus kas 6 bulan terakhir</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Perbandingan pemasukan dan pengeluaran</p>
                </div>
            </div>
            <div class="h-72 p-4 sm:p-5">
                <canvas id="cashflowChart"></canvas>
            </div>
        </div>

        <div class="card">
            <div class="border-b border-slate-100 px-5 py-4 dark:border-slate-700 sm:px-6">
                <h2 class="text-base font-semibold text-slate-900 dark:text-white">Pengeluaran per kategori</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">{{ nama_bulan($month) }}</p>
            </div>

            @if ($breakdown->isEmpty())
                <div class="flex flex-col items-center justify-center px-6 py-12 text-center">
                    <span class="text-3xl">📊</span>
                    <p class="mt-3 text-sm font-medium text-slate-700 dark:text-slate-200">Belum ada pengeluaran</p>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Catat transaksi pertamamu untuk melihat grafik.</p>
                    <a href="{{ route('transactions.create') }}" class="btn btn-primary btn-sm mt-4">Catat sekarang</a>
                </div>
            @else
                <div class="p-5 sm:p-6">
                    <div class="relative mx-auto h-40 w-40">
                        <canvas id="breakdownChart"></canvas>
                    </div>
                    <ul class="mt-5 space-y-2.5">
                        @foreach ($breakdown as $item)
                            <li class="flex items-center gap-3 text-sm">
                                <span class="h-2.5 w-2.5 shrink-0 rounded-full" style="background-color: {{ $item['color'] }}"></span>
                                <span class="flex-1 truncate text-slate-600 dark:text-slate-300">{{ $item['icon'] }} {{ $item['name'] }}</span>
                                <span class="font-medium text-slate-900 dark:text-slate-100">{{ rupiah($item['total']) }}</span>
                                <span class="w-10 text-right text-xs text-slate-500 dark:text-slate-400">{{ $breakdownTotal > 0 ? round($item['total'] / $breakdownTotal * 100) : 0 }}%</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 items-start gap-6 xl:grid-cols-3">
        <div class="card xl:col-span-2">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 dark:border-slate-700 sm:px-6">
                <div>
                    <h2 class="flex items-center gap-2 text-base font-semibold text-slate-900 dark:text-white"><span class="h-3.5 w-1 rounded-full bg-cyan-400"></span>Transaksi terbaru</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">6 transaksi terakhir yang kamu catat</p>
                </div>
                <a href="{{ route('transactions.index') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300">Lihat semua</a>
            </div>

            @if ($recentTransactions->isEmpty())
                <div class="flex flex-col items-center justify-center px-6 py-12 text-center">
                    <span class="text-3xl">🧾</span>
                    <p class="mt-3 text-sm font-medium text-slate-700 dark:text-slate-200">Belum ada transaksi</p>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Mulai catat pemasukan dan pengeluaranmu.</p>
                    <a href="{{ route('transactions.create') }}" class="btn btn-primary btn-sm mt-4">Tambah transaksi</a>
                </div>
            @else
                <div class="divide-y divide-slate-100 dark:divide-slate-700">
                    @foreach ($recentTransactions as $transaction)
                        <div class="flex items-center gap-3 px-5 py-3.5 sm:px-6">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-base" style="background-color: {{ $transaction->category->color ?? '#94a3b8' }}1a">
                                {{ $transaction->category->icon ?? '📦' }}
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-slate-900 dark:text-slate-100">{{ $transaction->category->name ?? 'Tanpa kategori' }}</p>
                                <p class="truncate text-xs text-slate-500 dark:text-slate-400">
                                    {{ tanggal_indonesia($transaction->date) }} · {{ $transaction->wallet->name ?? '-' }}{{ $transaction->note ? ' · '.$transaction->note : '' }}
                                </p>
                            </div>
                            <p class="shrink-0 text-sm font-bold {{ $transaction->type === 'income' ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-700 dark:text-rose-400' }}">
                                {{ $transaction->type === 'income' ? '+' : '-' }}{{ rupiah($transaction->amount) }}
                            </p>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="space-y-6">
            <div class="card">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 dark:border-slate-700">
                    <h2 class="flex items-center gap-2 text-base font-semibold text-slate-900 dark:text-white"><span class="h-3.5 w-1 rounded-full bg-cyan-400"></span>Budget bulan ini</h2>
                    <a href="{{ route('budgets.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300">Kelola</a>
                </div>

                @if ($budgets->isEmpty())
                    <div class="px-5 py-8 text-center">
                        <p class="text-sm text-slate-500 dark:text-slate-400">Belum ada budget. Tetapkan batas pengeluaran per kategori.</p>
                        <a href="{{ route('budgets.index') }}" class="btn btn-secondary btn-sm mt-3">Buat budget</a>
                    </div>
                @else
                    <div class="space-y-4 px-5 py-4">
                        @foreach ($budgets->take(4) as $budget)
                            @php($percent = min(100, $budget->percent))
                            @php($spent = $budget->spent())
                            <div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="truncate font-medium text-slate-700 dark:text-slate-200">{{ $budget->category->icon ?? '' }} {{ $budget->category->name ?? '-' }}</span>
                                    <span class="text-xs font-semibold {{ $budget->percent >= 100 ? 'text-rose-700 dark:text-rose-400' : ($budget->percent >= 80 ? 'text-amber-700 dark:text-amber-400' : 'text-slate-500 dark:text-slate-400') }}">
                                        {{ round($budget->percent) }}%
                                    </span>
                                </div>
                                <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                    <div class="h-1.5 rounded-full {{ $budget->percent >= 100 ? 'bg-rose-500' : ($budget->percent >= 80 ? 'bg-amber-500' : 'bg-emerald-500') }}" style="width: {{ $percent }}%"></div>
                                </div>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ rupiah($spent) }} dari {{ rupiah($budget->amount) }}</p>
                            </div>
                        @endforeach

                        @if ($overBudgets->isNotEmpty())
                            <p class="rounded-xl bg-amber-50 px-3 py-2 text-xs font-medium text-amber-800 dark:bg-amber-950/40 dark:text-amber-300">
                                <span class="font-semibold">{{ $overBudgets->count() }}</span> kategori sudah mencapai 80% budget.
                            </p>
                        @endif
                    </div>
                @endif
            </div>

            <div class="card">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 dark:border-slate-700">
                    <h2 class="text-base font-semibold text-slate-900 dark:text-white">Target tabungan</h2>
                    <a href="{{ route('goals.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300">Kelola</a>
                </div>

                @if ($goals->isEmpty())
                    <div class="px-5 py-8 text-center">
                        <p class="text-sm text-slate-500 dark:text-slate-400">Belum ada target. Yuk tentukan tujuan menabungmu.</p>
                        <a href="{{ route('goals.index') }}" class="btn btn-secondary btn-sm mt-3">Buat target</a>
                    </div>
                @else
                    <div class="space-y-4 px-5 py-4">
                        @foreach ($goals as $goal)
                            <div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="truncate font-medium text-slate-700 dark:text-slate-200">{{ $goal->name }}</span>
                                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">{{ $goal->progress }}%</span>
                                </div>
                                <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                    <div class="h-1.5 rounded-full" style="width: {{ $goal->progress }}%; background-color: {{ $goal->color }}"></div>
                                </div>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ rupiah($goal->saved_amount) }} dari {{ rupiah($goal->target_amount) }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('vendor/chart.umd.min.js') }}"></script>
    <script>
        (function () {
            if (typeof Chart === 'undefined') {
                return;
            }

            const theme = window.chartColors ? window.chartColors() : { tick: '#64748b', grid: '#f1f5f9' };

            Chart.defaults.font.family = "'Instrument Sans', ui-sans-serif, system-ui, sans-serif";
            Chart.defaults.color = theme.tick;

            window.__charts = window.__charts || [];

            const cashflow = document.getElementById('cashflowChart');
            if (cashflow) {
                window.__charts.push(new Chart(cashflow, {
                    type: 'bar',
                    data: {
                        labels: @json($chart->pluck('label')),
                        datasets: [
                            {
                                label: 'Pemasukan',
                                data: @json($chart->pluck('income')),
                                backgroundColor: '#10b981',
                                borderRadius: 6,
                                maxBarThickness: 22,
                            },
                            {
                                label: 'Pengeluaran',
                                data: @json($chart->pluck('expense')),
                                backgroundColor: '#ef4444',
                                borderRadius: 6,
                                maxBarThickness: 22,
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { usePointStyle: true, boxWidth: 8, padding: 16 },
                            },
                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                        return context.dataset.label + ': Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
                                    },
                                },
                            },
                        },
                        scales: {
                            x: { grid: { display: false }, border: { display: false } },
                            y: {
                                beginAtZero: true,
                                border: { display: false },
                                grid: { color: theme.grid },
                                ticks: {
                                    color: theme.tick,
                                    callback: function (value) {
                                        if (value >= 1000000) return (value / 1000000) + 'jt';
                                        if (value >= 1000) return (value / 1000) + 'rb';
                                        return value;
                                    },
                                },
                            },
                        },
                    },
                }));
            }

            const breakdown = document.getElementById('breakdownChart');
            if (breakdown) {
                window.__charts.push(new Chart(breakdown, {
                    type: 'doughnut',
                    data: {
                        labels: @json($breakdown->pluck('name')),
                        datasets: [{
                            data: @json($breakdown->pluck('total')),
                            backgroundColor: @json($breakdown->pluck('color')),
                            borderWidth: 0,
                            spacing: 2,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '68%',
                        animation: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                        return context.label + ': Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed);
                                    },
                                },
                            },
                        },
                    },
                }));
            }
        })();
    </script>
@endpush
