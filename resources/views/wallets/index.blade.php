@extends('layouts.app')

@section('title', 'Dompet')

@section('content')
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="title-page">Dompet</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Pisahkan uangmu berdasarkan sumber penyimpanan.</p>
        </div>
        <div class="rounded-2xl bg-blue-950 px-5 py-3 text-white ring-1 ring-blue-900">
            <p class="text-[11px] font-semibold uppercase tracking-widest text-blue-200">Total saldo</p>
            <p class="text-lg font-semibold">{{ rupiah($totalBalance) }}</p>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="xl:col-span-2">
            @if ($wallets->isEmpty())
                <div class="card flex flex-col items-center justify-center px-6 py-16 text-center">
                    <span class="text-4xl">👛</span>
                    <p class="mt-4 text-base font-semibold text-slate-800 dark:text-slate-100">Belum ada dompet</p>
                    <p class="mt-1 max-w-sm text-sm text-slate-500 dark:text-slate-400">Tambahkan dompet pertamamu untuk mulai mencatat saldo.</p>
                </div>
            @else
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    @foreach ($wallets as $wallet)
                        <div class="card card-body">
                            <div class="flex items-start justify-between">
                                <span class="flex h-10 w-10 items-center justify-center rounded-xl text-white" style="background-color: {{ $wallet->color }}">
                                    @include('partials.icon', ['name' => 'wallet', 'class' => 'h-5 w-5'])
                                </span>
                                <div class="flex items-center gap-1">
                                    <a href="{{ route('wallets.edit', $wallet) }}" class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-blue-600 dark:hover:bg-slate-800 dark:hover:text-blue-400" title="Edit">
                                        @include('partials.icon', ['name' => 'pencil', 'class' => 'h-4 w-4'])
                                    </a>
                                    <form method="POST" action="{{ route('wallets.destroy', $wallet) }}" onsubmit="return confirm('Hapus dompet ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg p-1.5 text-slate-400 transition hover:bg-rose-50 hover:text-rose-700 dark:hover:bg-rose-950/40 dark:hover:text-rose-400" title="Hapus">
                                            @include('partials.icon', ['name' => 'trash', 'class' => 'h-4 w-4'])
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <div class="mt-4">
                                <div class="flex items-center gap-2">
                                    <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $wallet->name }}</h2>
                                    <span class="badge bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-300">{{ $wallet->type_label }}</span>
                                    @unless ($wallet->is_active)
                                        <span class="badge bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400">Nonaktif</span>
                                    @endunless
                                </div>
                                <p class="mt-2 text-xl font-bold tracking-tight text-slate-900 dark:text-white">{{ rupiah($wallet->balance) }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Saldo awal {{ rupiah($wallet->initial_balance) }} &middot; {{ $wallet->transactions_count }} transaksi</p>
                            </div>

                            <div class="mt-3 flex flex-wrap gap-2">
                                <span class="badge bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400">Masuk {{ rupiah($wallet->income_total) }}</span>
                                <span class="badge bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400">Keluar {{ rupiah($wallet->expense_total) }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="card card-body h-fit">
            <h2 class="text-base font-semibold text-slate-900 dark:text-white">Tambah dompet</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Misalnya tunai, rekening bank, atau e-wallet.</p>

            <form method="POST" action="{{ route('wallets.store') }}" class="mt-5">
                @csrf
                @include('wallets._form', ['wallet' => null])

                <button type="submit" class="btn btn-primary mt-5 w-full">
                    @include('partials.icon', ['name' => 'plus', 'class' => 'h-4 w-4'])
                    Simpan dompet
                </button>
            </form>
        </div>
    </div>
@endsection
