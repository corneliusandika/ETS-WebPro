@extends('layouts.app')

@section('title', 'Transaksi')

@section('content')
    @php($net = $totalIncome - $totalExpense)

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="title-page">Transaksi</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Semua pemasukan dan pengeluaran yang kamu catat.</p>
        </div>
        <a href="{{ route('transactions.create') }}" class="btn btn-primary">
            @include('partials.icon', ['name' => 'plus', 'class' => 'h-4 w-4'])
            Tambah transaksi
        </a>
    </div>

    <form method="GET" action="{{ route('transactions.index') }}" class="card card-body mt-6">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <div>
                <label for="filter-month" class="label">Bulan</label>
                <select id="filter-month" name="month" class="input">
                    <option value="">Semua bulan</option>
                    @foreach ($monthOptions as $value => $label)
                        <option value="{{ $value }}" @selected($filters['month'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="filter-type" class="label">Jenis</label>
                <select id="filter-type" name="type" class="input">
                    <option value="">Semua jenis</option>
                    <option value="income" @selected($filters['type'] === 'income')>Pemasukan</option>
                    <option value="expense" @selected($filters['type'] === 'expense')>Pengeluaran</option>
                </select>
            </div>

            <div>
                <label for="filter-category" class="label">Kategori</label>
                <select id="filter-category" name="category_id" class="input">
                    <option value="">Semua kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) $filters['category_id'] === (string) $category->id)>
                            {{ $category->icon }} {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="filter-wallet" class="label">Dompet</label>
                <select id="filter-wallet" name="wallet_id" class="input">
                    <option value="">Semua dompet</option>
                    @foreach ($wallets as $wallet)
                        <option value="{{ $wallet->id }}" @selected((string) $filters['wallet_id'] === (string) $wallet->id)>{{ $wallet->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="filter-q" class="label">Cari catatan</label>
                <input id="filter-q" name="q" type="text" value="{{ $filters['q'] }}" class="input" placeholder="Kata kunci...">
            </div>
        </div>

        <div class="mt-4 flex items-center gap-2">
            <button type="submit" class="btn btn-primary btn-sm">
                @include('partials.icon', ['name' => 'search', 'class' => 'h-4 w-4'])
                Terapkan
            </button>
            <a href="{{ route('transactions.index') }}" class="btn btn-ghost btn-sm">Reset</a>
        </div>
    </form>

    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="card card-body">
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">Pemasukan</p>
            <p class="mt-1 text-xl font-bold text-emerald-700 dark:text-emerald-400">{{ rupiah($totalIncome) }}</p>
        </div>
        <div class="card card-body">
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">Pengeluaran</p>
            <p class="mt-1 text-xl font-bold text-rose-700 dark:text-rose-400">{{ rupiah($totalExpense) }}</p>
        </div>
        <div class="card card-body">
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">Selisih</p>
            <p class="mt-1 text-xl font-bold {{ $net >= 0 ? 'text-slate-900 dark:text-white' : 'text-rose-700 dark:text-rose-400' }}">
                {{ $net >= 0 ? '+' : '' }}{{ rupiah($net) }}
            </p>
        </div>
    </div>

    <div class="card mt-6 overflow-hidden">
        @if ($transactions->isEmpty())
            <div class="flex flex-col items-center justify-center px-6 py-16 text-center">
                <span class="text-4xl">🔍</span>
                <p class="mt-4 text-base font-semibold text-slate-800 dark:text-slate-100">Tidak ada transaksi</p>
                <p class="mt-1 max-w-sm text-sm text-slate-500 dark:text-slate-400">Coba ubah filter, atau catat transaksi baru untuk mulai mengisi riwayatmu.</p>
                <a href="{{ route('transactions.create') }}" class="btn btn-primary btn-sm mt-4">Tambah transaksi</a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 dark:divide-slate-700">
                    <thead class="bg-blue-50/70 dark:bg-blue-950/30">
                        <tr>
                            <th class="th">Tanggal</th>
                            <th class="th">Kategori</th>
                            <th class="th">Dompet</th>
                            <th class="th">Catatan</th>
                            <th class="th text-right">Nominal</th>
                            <th class="th text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white dark:divide-slate-700 dark:bg-slate-900">
                        @foreach ($transactions as $transaction)
                            <tr class="transition hover:bg-slate-50/70 dark:hover:bg-slate-800/40">
                                <td class="td whitespace-nowrap text-slate-500 dark:text-slate-400">{{ tanggal_indonesia($transaction->date) }}</td>
                                <td class="td">
                                    <div class="flex items-center gap-2.5">
                                        <span class="flex h-9 w-9 items-center justify-center rounded-xl text-sm" style="background-color: {{ $transaction->category->color ?? '#94a3b8' }}1a">
                                            {{ $transaction->category->icon ?? '📦' }}
                                        </span>
                                        <span class="font-medium text-slate-900 dark:text-slate-100">{{ $transaction->category->name ?? 'Tanpa kategori' }}</span>
                                    </div>
                                </td>
                                <td class="td whitespace-nowrap text-slate-500 dark:text-slate-400">{{ $transaction->wallet->name ?? '-' }}</td>
                                <td class="td max-w-xs truncate text-slate-500 dark:text-slate-400">{{ $transaction->note ?: '—' }}</td>
                                <td class="td whitespace-nowrap text-right font-bold {{ $transaction->type === 'income' ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-700 dark:text-rose-400' }}">
                                    {{ $transaction->type === 'income' ? '+' : '-' }}{{ rupiah($transaction->amount) }}
                                </td>
                                <td class="td">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('transactions.edit', $transaction) }}" class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-blue-600 dark:hover:bg-slate-800 dark:hover:text-blue-400" title="Edit">
                                            @include('partials.icon', ['name' => 'pencil', 'class' => 'h-4 w-4'])
                                        </a>
                                        <form method="POST" action="{{ route('transactions.destroy', $transaction) }}" onsubmit="return confirm('Hapus transaksi ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg p-2 text-slate-400 transition hover:bg-rose-50 hover:text-rose-700 dark:hover:bg-rose-950/40 dark:hover:text-rose-400" title="Hapus">
                                                @include('partials.icon', ['name' => 'trash', 'class' => 'h-4 w-4'])
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-100 px-5 py-4 dark:border-slate-700">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
@endsection
