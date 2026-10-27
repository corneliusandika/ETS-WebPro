@extends('layouts.app')

@section('title', 'Edit Dompet')

@section('content')
    <div class="flex items-center gap-3">
        <a href="{{ route('wallets.index') }}" class="flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:text-slate-800 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-400 dark:hover:text-slate-100">
            @include('partials.icon', ['name' => 'left', 'class' => 'h-4 w-4'])
        </a>
        <div>
            <h1 class="title-page">Edit dompet</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Perbarui nama, jenis, saldo awal, atau status dompet.</p>
        </div>
    </div>

    <div class="mt-6 max-w-2xl space-y-6">
        <form method="POST" action="{{ route('wallets.update', $wallet) }}" class="card card-body">
            @csrf
            @method('PUT')
            @include('wallets._form')

            <div class="mt-6 flex items-center gap-3">
                <button type="submit" class="btn btn-primary">Simpan perubahan</button>
                <a href="{{ route('wallets.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>

        <div class="card card-body border-rose-200 dark:border-rose-900/60">
            <h2 class="text-sm font-semibold text-rose-700 dark:text-rose-400">Hapus dompet</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Dompet yang masih memiliki transaksi tidak bisa dihapus.</p>
            <form method="POST" action="{{ route('wallets.destroy', $wallet) }}" class="mt-4" onsubmit="return confirm('Hapus dompet ini?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">Hapus dompet</button>
            </form>
        </div>
    </div>
@endsection
