<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50 dark:bg-slate-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Masuk') · {{ config('app.name', 'Dompetku') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/dompetku-mark.svg') }}">

    <script>
        (function () {
            try {
                var stored = localStorage.getItem('theme');
                var isDark = stored ? stored === 'dark' : window.matchMedia('(prefers-color-scheme: dark)').matches;
                if (isDark) {
                    document.documentElement.classList.add('dark');
                }
            } catch (error) {}
        })();
    </script>

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-slate-50 text-slate-800 antialiased dark:bg-slate-900 dark:text-slate-100">
    <div class="relative isolate flex min-h-screen overflow-hidden">
        <div class="pointer-events-none absolute inset-0 -z-10">
            <div class="absolute inset-0 bg-gradient-to-br from-blue-200/70 via-slate-50 to-cyan-100/70 dark:from-slate-900 dark:via-slate-900 dark:to-blue-950"></div>
            <div class="absolute -left-40 top-0 h-[32rem] w-[32rem] rounded-full bg-blue-400/35 blur-3xl dark:bg-blue-600/20"></div>
            <div class="absolute -bottom-40 -right-32 h-[32rem] w-[32rem] rounded-full bg-cyan-400/25 blur-3xl dark:bg-cyan-600/20"></div>
            <div class="absolute inset-0 bg-grid opacity-100 dark:opacity-50"></div>
        </div>

        <aside class="relative hidden w-1/2 flex-col overflow-hidden bg-blue-950 p-12 lg:flex xl:p-16">
            <div class="absolute -left-24 -top-24 h-96 w-96 rounded-full bg-blue-600/30 blur-3xl"></div>
            <div class="absolute -bottom-32 -right-16 h-96 w-96 rounded-full bg-cyan-500/20 blur-3xl"></div>
            <div class="pointer-events-none absolute inset-0 bg-grid opacity-30"></div>

            <div class="relative flex h-full w-full flex-col">
                <a href="{{ url('/') }}" class="flex items-center gap-3">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white shadow-lg shadow-black/20">
                        <img src="{{ asset('images/dompetku-mark.svg') }}" alt="Dompetku" class="h-9 w-9">
                    </span>
                    <span>
                        <span class="block text-lg font-bold uppercase tracking-[0.14em] text-white">Dompetku</span>
                        <span class="block text-[11px] text-slate-400">Personal expense tracker</span>
                    </span>
                </a>

                <div class="my-auto max-w-md py-10">
                    <h2 class="text-4xl font-bold leading-tight tracking-tight text-white">
                        Kendalikan uangmu,<br>
                        <span class="bg-gradient-to-r from-blue-300 to-cyan-300 bg-clip-text text-transparent">bukan sebaliknya.</span>
                    </h2>
                    <p class="mt-4 text-sm leading-relaxed text-slate-400">
                        Catat pemasukan dan pengeluaran harian, pantau budget per kategori, dan capai target tabunganmu dalam satu tempat.
                    </p>

                    <div class="mt-8 rounded-2xl border border-white/10 bg-white/5 p-4 ring-1 ring-white/5 backdrop-blur">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full bg-rose-400/70"></span>
                                <span class="h-2 w-2 rounded-full bg-amber-400/70"></span>
                                <span class="h-2 w-2 rounded-full bg-emerald-400/70"></span>
                            </div>
                            <span class="text-[10px] uppercase tracking-widest text-slate-500">Ringkasan</span>
                        </div>

                        <div class="mt-4 grid grid-cols-3 gap-2">
                            <div class="rounded-xl bg-white/5 p-2.5 ring-1 ring-white/5">
                                <p class="text-[9px] uppercase tracking-wider text-slate-500">Saldo</p>
                                <div class="mt-2 h-2.5 w-14 rounded bg-white/25"></div>
                            </div>
                            <div class="rounded-xl bg-white/5 p-2.5 ring-1 ring-white/5">
                                <p class="text-[9px] uppercase tracking-wider text-slate-500">Masuk</p>
                                <div class="mt-2 h-2.5 w-12 rounded bg-emerald-400/40"></div>
                            </div>
                            <div class="rounded-xl bg-white/5 p-2.5 ring-1 ring-white/5">
                                <p class="text-[9px] uppercase tracking-wider text-slate-500">Keluar</p>
                                <div class="mt-2 h-2.5 w-10 rounded bg-rose-400/40"></div>
                            </div>
                        </div>

                        <div class="mt-4 flex h-24 items-end gap-1.5 rounded-xl bg-white/5 p-3 ring-1 ring-white/5">
                            <div class="h-8 w-full rounded-sm bg-blue-400/30"></div>
                            <div class="h-14 w-full rounded-sm bg-emerald-400/50"></div>
                            <div class="h-10 w-full rounded-sm bg-blue-400/30"></div>
                            <div class="h-16 w-full rounded-sm bg-emerald-400/50"></div>
                            <div class="h-11 w-full rounded-sm bg-blue-400/30"></div>
                            <div class="h-20 w-full rounded-sm bg-emerald-400/50"></div>
                            <div class="h-12 w-full rounded-sm bg-blue-400/30"></div>
                        </div>
                    </div>

                    <div class="mt-6 grid grid-cols-2 gap-2.5">
                        <div class="flex items-center gap-2.5 rounded-xl bg-white/5 px-3 py-2.5 ring-1 ring-white/10">
                            @include('partials.icon', ['name' => 'wallet', 'class' => 'h-4 w-4 text-cyan-300'])
                            <span class="text-xs font-medium text-slate-300">Multi dompet</span>
                        </div>
                        <div class="flex items-center gap-2.5 rounded-xl bg-white/5 px-3 py-2.5 ring-1 ring-white/10">
                            @include('partials.icon', ['name' => 'chart', 'class' => 'h-4 w-4 text-cyan-300'])
                            <span class="text-xs font-medium text-slate-300">Budget kategori</span>
                        </div>
                        <div class="flex items-center gap-2.5 rounded-xl bg-white/5 px-3 py-2.5 ring-1 ring-white/10">
                            @include('partials.icon', ['name' => 'flag', 'class' => 'h-4 w-4 text-cyan-300'])
                            <span class="text-xs font-medium text-slate-300">Target tabungan</span>
                        </div>
                        <div class="flex items-center gap-2.5 rounded-xl bg-white/5 px-3 py-2.5 ring-1 ring-white/10">
                            @include('partials.icon', ['name' => 'report', 'class' => 'h-4 w-4 text-cyan-300'])
                            <span class="text-xs font-medium text-slate-300">CSV &amp; PDF</span>
                        </div>
                    </div>
                </div>

                <p class="relative text-xs text-slate-400">Data keuanganmu tetap milikmu.</p>
            </div>
        </aside>

        <main class="relative flex w-full items-center justify-center px-4 py-10 lg:w-1/2 lg:border-l lg:border-slate-200/60 lg:bg-white/35 lg:py-12 lg:backdrop-blur-xl dark:lg:border-slate-700/60 dark:lg:bg-slate-900/40">
            <button type="button" data-theme-toggle class="absolute right-4 top-4 inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-600 shadow-sm transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700" title="Ganti tema">
                <span class="dark:hidden">@include('partials.icon', ['name' => 'moon', 'class' => 'h-4 w-4'])</span>
                <span class="hidden dark:inline">@include('partials.icon', ['name' => 'sun', 'class' => 'h-4 w-4'])</span>
                <span class="dark:hidden">Gelap</span>
                <span class="hidden dark:inline">Terang</span>
            </button>

            <div class="w-full max-w-md">
                <div class="mb-8 lg:hidden">
                    <div class="flex items-center gap-3">
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 dark:ring-white/10">
                            <img src="{{ asset('images/dompetku-mark.svg') }}" alt="Dompetku" class="h-9 w-9">
                        </span>
                        <span>
                            <span class="block text-lg font-bold uppercase tracking-[0.14em]">Dompetku</span>
                            <span class="block text-[11px] text-slate-500 dark:text-slate-400">Personal expense tracker</span>
                        </span>
                    </div>
                    <p class="mt-4 text-sm text-slate-500 dark:text-slate-400">
                        Catat pemasukan, pantau budget, dan capai target tabunganmu dalam satu tempat.
                    </p>
                </div>

                @if (session('success'))
                    <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-300">
                        {{ session('success') }}
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>
