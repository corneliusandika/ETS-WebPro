@extends('layouts.app')

@section('title', 'Catat Transaksi')

@section('content')
    <div class="flex items-center gap-3">
        <a href="{{ route('transactions.index') }}" class="flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:text-slate-800 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-400 dark:hover:text-slate-100">
            @include('partials.icon', ['name' => 'left', 'class' => 'h-4 w-4'])
        </a>
        <div>
            <h1 class="title-page">Catat transaksi</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Tambahkan pemasukan atau pengeluaran baru.</p>
        </div>
    </div>

    <div class="mt-6 max-w-2xl">
        <form method="POST" action="{{ route('transactions.store') }}" class="card card-body">
            @csrf
            @include('transactions._form', ['transaction' => null])

            <div class="mt-6 flex items-center gap-3">
                <button type="submit" class="btn btn-primary">
                    @include('partials.icon', ['name' => 'check', 'class' => 'h-4 w-4'])
                    Simpan transaksi
                </button>
                <a href="{{ route('transactions.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
@endsection
