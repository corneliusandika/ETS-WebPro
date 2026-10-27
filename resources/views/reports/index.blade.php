@extends('layouts.app')

@section('title', 'Laporan Bulanan')

@section('content')
    @php
        $net = $income - $expense;
        $expenseTotal = $expenseByCategory->sum('total');
        $prevMonth = $month->copy()->subMonth()->format('Y-m');
        $nextMonth = $month->copy()->addMonth()->format('Y-m');
    @endphp

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="title-page">Laporan {{ nama_bulan($month) }}</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Rekap pemasukan, pengeluaran, dan performa per kategori.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('reports.index', ['month' => $prevMonth]) }}" class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:text-slate-800 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-400 dark:hover:text-slate-100">
                @include('partials.icon', ['name' => 'left', 'class' => 'h-4 w-4'])
            </a>
            <form method="GET" action="{{ route('reports.index') }}">
                <select name="month" onchange="this.form.submit()" class="input w-auto">
                    @foreach ($monthOptions as $value => $label)
                        <option value="{{ $value }}" @selected($month->format('Y-m') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </form>
            <a href="{{ route('reports.index', ['month' => $nextMonth]) }}" class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:text-slate-800 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-400 dark:hover:text-slate-100">
                @include('partials.icon', ['name' => 'right', 'class' => 'h-4 w-4'])
            </a>
            <a href="{{ route('reports.export', ['month' => $month->format('Y-m')]) }}" class="btn btn-secondary">
                @include('partials.icon', ['name' => 'download', 'class' => 'h-4 w-4'])
                CSV
            </a>
            <a href="{{ route('reports.pdf', ['month' => $month->format('Y-m')]) }}" class="btn btn-secondary">
                @include('partials.icon', ['name' => 'file', 'class' => 'h-4 w-4'])
                PDF
            </a>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="card card-body">
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">Pemasukan</p>
            <p class="mt-1 text-xl font-bold text-emerald-700 dark:text-emerald-400">{{ rupiah($income) }}</p>
        </div>
        <div class="card card-body">
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">Pengeluaran</p>
            <p class="mt-1 text-xl font-bold text-rose-700 dark:text-rose-400">{{ rupiah($expense) }}</p>
            @if ($expenseDelta !== null)
                <p class="mt-1 text-xs font-medium {{ $expenseDelta > 0 ? 'text-rose-500 dark:text-rose-400' : 'text-emerald-700 dark:text-emerald-400' }}">
                    {{ $expenseDelta > 0 ? '▲' : '▼' }} {{ abs($expenseDelta) }}% dibanding bulan lalu
                </p>
            @else
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Belum ada pembanding</p>
            @endif
        </div>
        <div class="card card-body">
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">Selisih</p>
            <p class="mt-1 text-xl font-bold {{ $net >= 0 ? 'text-slate-900 dark:text-white' : 'text-rose-700 dark:text-rose-400' }}">{{ $net >= 0 ? '+' : '' }}{{ rupiah($net) }}</p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400"><span class="font-semibold text-slate-700 dark:text-slate-200">{{ $transactions->count() }}</span> transaksi</p>
        </div>
        <div class="card card-body">
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">Pengeluaran terbesar</p>
            @if ($biggestExpense)
                <p class="mt-1 text-xl font-bold text-slate-900 dark:text-white">{{ rupiah($biggestExpense->amount) }}</p>
                <p class="mt-1 truncate text-xs text-slate-500 dark:text-slate-400">{{ $biggestExpense->category->name ?? 'Tanpa kategori' }} · {{ tanggal_indonesia($biggestExpense->date) }}</p>
            @else
                <p class="mt-1 text-xl font-bold text-slate-500 dark:text-slate-400">—</p>
            @endif
        </div>
    </div>

    <div class="mt-6 card">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 dark:border-slate-700 sm:px-6">
            <div>
                <h2 class="text-base font-semibold text-slate-900 dark:text-white">Arus kas harian</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Pemasukan dan pengeluaran per tanggal</p>
            </div>
        </div>
        <div class="h-72 p-4 sm:p-5">
            <canvas id="dailyChart"></canvas>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-2">
        <div class="card">
            <div class="border-b border-slate-100 px-5 py-4 dark:border-slate-700 sm:px-6">
                <h2 class="text-base font-semibold text-slate-900 dark:text-white">Pengeluaran per kategori</h2>
            </div>

            @if ($expenseByCategory->isEmpty())
                <p class="px-6 py-10 text-center text-sm text-slate-500 dark:text-slate-400">Belum ada pengeluaran bulan ini.</p>
            @else
                <ul class="divide-y divide-slate-100 dark:divide-slate-700">
                    @foreach ($expenseByCategory as $item)
                        @php($percent = $expenseTotal > 0 ? round($item['total'] / $expenseTotal * 100, 1) : 0)
                        <li class="flex items-center gap-3 px-5 py-3.5 sm:px-6">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-base" style="background-color: {{ $item['color'] }}1a">{{ $item['icon'] }}</span>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="truncate text-sm font-medium text-slate-900 dark:text-slate-100">{{ $item['name'] }}</p>
                                    <p class="shrink-0 text-sm font-bold text-slate-900 dark:text-slate-100">{{ rupiah($item['total']) }}</p>
                                </div>
                                <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                    <div class="h-1.5 rounded-full" style="width: {{ $percent }}%; background-color: {{ $item['color'] }}"></div>
                                </div>
                            </div>
                            <span class="w-12 shrink-0 text-right text-xs font-semibold text-slate-500 dark:text-slate-400">{{ $percent }}%</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="space-y-6">
            <div class="card">
                <div class="border-b border-slate-100 px-5 py-4 dark:border-slate-700 sm:px-6">
                    <h2 class="text-base font-semibold text-slate-900 dark:text-white">Pemasukan per kategori</h2>
                </div>

                @if ($incomeByCategory->isEmpty())
                    <p class="px-6 py-8 text-center text-sm text-slate-500 dark:text-slate-400">Belum ada pemasukan bulan ini.</p>
                @else
                    <ul class="divide-y divide-slate-100 dark:divide-slate-700">
                        @foreach ($incomeByCategory as $item)
                            <li class="flex items-center gap-3 px-5 py-3 sm:px-6">
                                <span class="h-2.5 w-2.5 shrink-0 rounded-full" style="background-color: {{ $item['color'] }}"></span>
                                <p class="flex-1 truncate text-sm text-slate-700 dark:text-slate-300">{{ $item['name'] }}</p>
                                <p class="text-sm font-semibold text-emerald-700 dark:text-emerald-400">{{ rupiah($item['total']) }}</p>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="card">
                <div class="border-b border-slate-100 px-5 py-4 dark:border-slate-700 sm:px-6">
                    <h2 class="text-base font-semibold text-slate-900 dark:text-white">Aktivitas per dompet</h2>
                </div>

                @if ($byWallet->isEmpty())
                    <p class="px-6 py-8 text-center text-sm text-slate-500 dark:text-slate-400">Belum ada aktivitas dompet bulan ini.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-100 dark:divide-slate-700">
                            <thead class="bg-blue-50/70 dark:bg-blue-950/30">
                                <tr>
                                    <th class="th">Dompet</th>
                                    <th class="th text-right">Masuk</th>
                                    <th class="th text-right">Keluar</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                @foreach ($byWallet as $item)
                                    <tr>
                                        <td class="td">
                                            <span class="flex items-center gap-2">
                                                <span class="h-2.5 w-2.5 rounded-full" style="background-color: {{ $item['color'] }}"></span>
                                                {{ $item['name'] }}
                                            </span>
                                        </td>
                                        <td class="td text-right font-medium text-emerald-700 dark:text-emerald-400">{{ rupiah($item['income']) }}</td>
                                        <td class="td text-right font-medium text-rose-700 dark:text-rose-400">{{ rupiah($item['expense']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
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
            const canvas = document.getElementById('dailyChart');
            if (!canvas || typeof Chart === 'undefined') {
                return;
            }

            const theme = window.chartColors ? window.chartColors() : { tick: '#64748b', grid: '#f1f5f9' };

            Chart.defaults.font.family = "'Instrument Sans', ui-sans-serif, system-ui, sans-serif";
            Chart.defaults.color = theme.tick;

            window.__charts = window.__charts || [];

            window.__charts.push(new Chart(canvas, {
                type: 'line',
                data: {
                    labels: @json($daily->pluck('day')),
                    datasets: [
                        {
                            label: 'Pemasukan',
                            data: @json($daily->pluck('income')),
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.08)',
                            fill: true,
                            tension: 0.35,
                            pointRadius: 0,
                            borderWidth: 2,
                        },
                        {
                            label: 'Pengeluaran',
                            data: @json($daily->pluck('expense')),
                            borderColor: '#ef4444',
                            backgroundColor: 'rgba(244, 63, 94, 0.08)',
                            fill: true,
                            tension: 0.35,
                            pointRadius: 0,
                            borderWidth: 2,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: false,
                    interaction: { mode: 'index', intersect: false },
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
        })();
    </script>
@endpush
