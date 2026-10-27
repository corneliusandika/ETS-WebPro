@extends('layouts.app')

@section('title', 'Target Tabungan')

@section('content')
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="title-page">Target Tabungan</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Tetapkan tujuan finansial dan pantau progresnya.</p>
        </div>
        <div class="flex gap-2">
            <div class="rounded-2xl bg-white px-4 py-2.5 shadow-sm ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-slate-700">
                <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">Total terkumpul</p>
                <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ rupiah($totalSaved) }} / {{ rupiah($totalTarget) }}</p>
            </div>
            <div class="rounded-2xl bg-emerald-50 px-4 py-2.5 ring-1 ring-emerald-100 dark:bg-emerald-950/40 dark:ring-emerald-900/60">
                <p class="text-[11px] font-semibold uppercase tracking-widest text-emerald-700 dark:text-emerald-300">Tercapai</p>
                <p class="text-sm font-semibold text-emerald-700 dark:text-emerald-300">{{ $completedCount }} target</p>
            </div>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="xl:col-span-2">
            @if ($goals->isEmpty())
                <div class="card flex flex-col items-center justify-center px-6 py-16 text-center">
                    <span class="text-4xl">🏁</span>
                    <p class="mt-4 text-base font-semibold text-slate-800 dark:text-slate-100">Belum ada target tabungan</p>
                    <p class="mt-1 max-w-sm text-sm text-slate-500 dark:text-slate-400">Mulai dari hal kecil: dana darurat, liburan, atau gadget baru.</p>
                </div>
            @else
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    @foreach ($goals as $goal)
                        <div class="card card-body">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="h-2.5 w-2.5 rounded-full" style="background-color: {{ $goal->color }}"></span>
                                        <h2 class="truncate text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $goal->name }}</h2>
                                    </div>
                                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                        @if ($goal->is_completed)
                                            Target tercapai 🎉
                                        @elseif ($goal->target_date)
                                            Target {{ tanggal_indonesia($goal->target_date) }}
                                        @else
                                            Tanpa tenggat waktu
                                        @endif
                                    </p>
                                </div>
                                <div class="flex items-center gap-1">
                                    <a href="{{ route('goals.edit', $goal) }}" class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-blue-600 dark:hover:bg-slate-800 dark:hover:text-blue-400" title="Edit">
                                        @include('partials.icon', ['name' => 'pencil', 'class' => 'h-4 w-4'])
                                    </a>
                                    <form method="POST" action="{{ route('goals.destroy', $goal) }}" onsubmit="return confirm('Hapus target ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg p-1.5 text-slate-400 transition hover:bg-rose-50 hover:text-rose-700 dark:hover:bg-rose-950/40 dark:hover:text-rose-400" title="Hapus">
                                            @include('partials.icon', ['name' => 'trash', 'class' => 'h-4 w-4'])
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <p class="mt-4 text-lg font-bold tracking-tight text-slate-900 dark:text-white">{{ rupiah($goal->saved_amount) }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">dari {{ rupiah($goal->target_amount) }}</p>

                            <div class="mt-2.5 h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                <div class="h-2 rounded-full" style="width: {{ $goal->progress }}%; background-color: {{ $goal->color }}"></div>
                            </div>
                            <div class="mt-1.5 flex items-center justify-between text-xs">
                                <span class="font-semibold text-slate-600 dark:text-slate-300">{{ $goal->progress }}%</span>
                                <span class="text-slate-500 dark:text-slate-400">
                                    {{ $goal->is_completed ? 'Selesai' : 'Sisa '.rupiah($goal->remaining) }}
                                </span>
                            </div>

                            @unless ($goal->is_completed)
                                <form method="POST" action="{{ route('goals.deposit', $goal) }}" class="mt-4 flex items-center gap-2">
                                    @csrf
                                    <div class="relative flex-1">
                                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-xs font-medium text-slate-500 dark:text-slate-400">Rp</span>
                                        <input name="amount" type="number" min="1" step="1000" required class="input py-2 pl-8 text-sm" placeholder="Setor dana">
                                    </div>
                                    <button type="submit" class="btn btn-secondary btn-sm shrink-0">Setor</button>
                                </form>
                            @endunless
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="card card-body h-fit">
            <h2 class="text-base font-semibold text-slate-900 dark:text-white">Buat target baru</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Tentukan tujuan dan mulai sisihkan uangmu.</p>

            <form method="POST" action="{{ route('goals.store') }}" class="mt-5">
                @csrf
                @include('goals._form', ['goal' => null])

                <button type="submit" class="btn btn-primary mt-5 w-full">
                    @include('partials.icon', ['name' => 'plus', 'class' => 'h-4 w-4'])
                    Simpan target
                </button>
            </form>
        </div>
    </div>
@endsection
