@extends('layouts.app')

@section('title', 'Budget')

@section('content')
    @php
        $remaining = $totalBudget - $totalSpent;
        $overallPercent = $totalBudget > 0 ? min(100, round($totalSpent / $totalBudget * 100, 1)) : 0;
        $prevMonth = $month->copy()->subMonth()->format('Y-m');
        $nextMonth = $month->copy()->addMonth()->format('Y-m');
    @endphp

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="title-page">Budget</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Tetapkan batas pengeluaran per kategori setiap bulan.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('budgets.index', ['month' => $prevMonth]) }}" class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:text-slate-800 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-400 dark:hover:text-slate-100">
                @include('partials.icon', ['name' => 'left', 'class' => 'h-4 w-4'])
            </a>
            <form method="GET" action="{{ route('budgets.index') }}">
                <select name="month" onchange="this.form.submit()" class="input w-auto">
                    @foreach ($monthOptions as $value => $label)
                        <option value="{{ $value }}" @selected($month->format('Y-m') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </form>
            <a href="{{ route('budgets.index', ['month' => $nextMonth]) }}" class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:text-slate-800 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-400 dark:hover:text-slate-100">
                @include('partials.icon', ['name' => 'right', 'class' => 'h-4 w-4'])
            </a>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="card card-body">
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">Total budget</p>
            <p class="mt-1 text-xl font-bold text-slate-900 dark:text-white">{{ rupiah($totalBudget) }}</p>
        </div>
        <div class="card card-body">
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">Terpakai</p>
            <p class="mt-1 text-xl font-bold text-rose-700 dark:text-rose-400">{{ rupiah($totalSpent) }}</p>
        </div>
        <div class="card card-body">
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">Sisa</p>
            <p class="mt-1 text-xl font-bold {{ $remaining >= 0 ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-700 dark:text-rose-400' }}">{{ rupiah($remaining) }}</p>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="space-y-4 xl:col-span-2">
            @if ($totalBudget > 0)
                <div class="card card-body">
                    <div class="flex items-center justify-between text-sm">
                        <span class="font-medium text-slate-700 dark:text-slate-200">Pemakaian budget keseluruhan</span>
                        <span class="font-semibold {{ $overallPercent >= 100 ? 'text-rose-700 dark:text-rose-400' : ($overallPercent >= 80 ? 'text-amber-700 dark:text-amber-400' : 'text-emerald-700 dark:text-emerald-400') }}">{{ $overallPercent }}%</span>
                    </div>
                    <div class="mt-2 h-2.5 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                        <div class="h-2.5 rounded-full {{ $overallPercent >= 100 ? 'bg-rose-500' : ($overallPercent >= 80 ? 'bg-amber-500' : 'bg-emerald-500') }}" style="width: {{ $overallPercent }}%"></div>
                    </div>
                </div>
            @endif

            @if ($budgets->isEmpty())
                <div class="card flex flex-col items-center justify-center px-6 py-16 text-center">
                    <span class="text-4xl">🎯</span>
                    <p class="mt-4 text-base font-semibold text-slate-800 dark:text-slate-100">Belum ada budget untuk {{ nama_bulan($month) }}</p>
                    <p class="mt-1 max-w-sm text-sm text-slate-500 dark:text-slate-400">Tentukan batas pengeluaran agar keuanganmu lebih terkontrol.</p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach ($budgets as $budget)
                        @php($spent = (float) ($spentMap[$budget->category_id] ?? 0))
                        @php($percent = (float) $budget->amount > 0 ? round($spent / (float) $budget->amount * 100, 1) : 0)
                        @php($left = (float) $budget->amount - $spent)
                        <div class="card card-body">
                            <div class="flex items-start gap-3">
                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-base" style="background-color: {{ $budget->category->color ?? '#94a3b8' }}1a">
                                    {{ $budget->category->icon ?? '📦' }}
                                </span>
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $budget->category->name ?? 'Tanpa kategori' }}</h2>
                                        <span class="badge {{ $percent >= 100 ? 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400' : ($percent >= 80 ? 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400' : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400') }}">
                                            {{ $percent >= 100 ? 'Melebihi budget' : ($percent >= 80 ? 'Hampir habis' : 'Aman') }}
                                        </span>
                                    </div>
                                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ rupiah($spent) }} dari {{ rupiah($budget->amount) }}</p>

                                    <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                        <div class="h-2 rounded-full {{ $percent >= 100 ? 'bg-rose-500' : ($percent >= 80 ? 'bg-amber-500' : 'bg-emerald-500') }}" style="width: {{ min(100, $percent) }}%"></div>
                                    </div>

                                    <p class="mt-1.5 text-xs font-medium {{ $left >= 0 ? 'text-slate-500 dark:text-slate-400' : 'text-rose-700 dark:text-rose-400' }}">
                                        {{ $left >= 0 ? 'Sisa '.rupiah($left) : 'Kelebihan '.rupiah(abs($left)) }} &middot; {{ $percent }}% terpakai
                                    </p>
                                </div>
                                <div class="flex items-center gap-1">
                                    <a href="{{ route('budgets.edit', $budget) }}" class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-blue-600 dark:hover:bg-slate-800 dark:hover:text-blue-400" title="Edit">
                                        @include('partials.icon', ['name' => 'pencil', 'class' => 'h-4 w-4'])
                                    </a>
                                    <form method="POST" action="{{ route('budgets.destroy', $budget) }}" onsubmit="return confirm('Hapus budget ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg p-2 text-slate-400 transition hover:bg-rose-50 hover:text-rose-700 dark:hover:bg-rose-950/40 dark:hover:text-rose-400" title="Hapus">
                                            @include('partials.icon', ['name' => 'trash', 'class' => 'h-4 w-4'])
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="card card-body h-fit">
            <h2 class="text-base font-semibold text-slate-900 dark:text-white">Buat budget</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Satu kategori hanya boleh punya satu budget per bulan.</p>

            <form method="POST" action="{{ route('budgets.store') }}" class="mt-5">
                @csrf
                @include('budgets._form', ['budget' => null])

                <button type="submit" class="btn btn-primary mt-5 w-full">
                    @include('partials.icon', ['name' => 'plus', 'class' => 'h-4 w-4'])
                    Simpan budget
                </button>
            </form>
        </div>
    </div>
@endsection
