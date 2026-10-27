@extends('layouts.app')

@section('title', 'Kategori')

@section('content')
    <div>
        <h1 class="title-page">Kategori</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Kelompokkan transaksi agar laporan lebih mudah dibaca.</p>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            @foreach ([['title' => 'Pengeluaran', 'items' => $expenseCategories, 'accent' => 'rose'], ['title' => 'Pemasukan', 'items' => $incomeCategories, 'accent' => 'emerald']] as $group)
                <div class="card overflow-hidden">
                    <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 dark:border-slate-700 sm:px-6">
                        <h2 class="text-base font-semibold text-slate-900 dark:text-white">{{ $group['title'] }}</h2>
                        <span class="badge {{ $group['accent'] === 'rose' ? 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400' : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400' }}">
                            {{ $group['items']->count() }} kategori
                        </span>
                    </div>

                    @if ($group['items']->isEmpty())
                        <div class="px-6 py-10 text-center text-sm text-slate-500 dark:text-slate-400">
                            Belum ada kategori {{ strtolower($group['title']) }}. Tambahkan lewat form di samping.
                        </div>
                    @else
                        <ul class="divide-y divide-slate-100 dark:divide-slate-700">
                            @foreach ($group['items'] as $category)
                                <li class="flex items-center gap-3 px-5 py-3.5 sm:px-6">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-base" style="background-color: {{ $category->color }}1a">
                                        {{ $category->icon }}
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-medium text-slate-900 dark:text-slate-100">{{ $category->name }}</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $category->transactions_count }} transaksi</p>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <a href="{{ route('categories.edit', $category) }}" class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-blue-600 dark:hover:bg-slate-800 dark:hover:text-blue-400" title="Edit">
                                            @include('partials.icon', ['name' => 'pencil', 'class' => 'h-4 w-4'])
                                        </a>
                                        <form method="POST" action="{{ route('categories.destroy', $category) }}" onsubmit="return confirm('Hapus kategori ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg p-2 text-slate-400 transition hover:bg-rose-50 hover:text-rose-700 dark:hover:bg-rose-950/40 dark:hover:text-rose-400" title="Hapus">
                                                @include('partials.icon', ['name' => 'trash', 'class' => 'h-4 w-4'])
                                            </button>
                                        </form>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="card card-body h-fit">
            <h2 class="text-base font-semibold text-slate-900 dark:text-white">Tambah kategori</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Buat kategori baru sesuai kebutuhanmu.</p>

            <form method="POST" action="{{ route('categories.store') }}" class="mt-5">
                @csrf
                @include('categories._form', ['category' => null])

                <button type="submit" class="btn btn-primary mt-5 w-full">
                    @include('partials.icon', ['name' => 'plus', 'class' => 'h-4 w-4'])
                    Simpan kategori
                </button>
            </form>
        </div>
    </div>
@endsection
