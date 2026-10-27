<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50 dark:bg-slate-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') · {{ config('app.name', 'Dompetku') }}</title>
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

            window.chartColors = function () {
                var isDark = document.documentElement.classList.contains('dark');

                return {
                    tick: isDark ? '#94a3b8' : '#64748b',
                    grid: isDark ? '#1e293b' : '#f1f5f9',
                };
            };
        })();
    </script>

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-slate-50 text-slate-800 antialiased dark:bg-slate-900 dark:text-slate-100">
    @php
        $navigation = [
            ['route' => 'dashboard', 'pattern' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'home'],
            ['route' => 'transactions.index', 'pattern' => 'transactions.*', 'label' => 'Transaksi', 'icon' => 'arrows'],
            ['route' => 'categories.index', 'pattern' => 'categories.*', 'label' => 'Kategori', 'icon' => 'tag'],
            ['route' => 'wallets.index', 'pattern' => 'wallets.*', 'label' => 'Dompet', 'icon' => 'wallet'],
            ['route' => 'budgets.index', 'pattern' => 'budgets.*', 'label' => 'Budget', 'icon' => 'chart'],
            ['route' => 'goals.index', 'pattern' => 'goals.*', 'label' => 'Target Tabungan', 'icon' => 'flag'],
            ['route' => 'reports.index', 'pattern' => 'reports.*', 'label' => 'Laporan', 'icon' => 'report'],
            ['route' => 'profile.edit', 'pattern' => 'profile.*', 'label' => 'Profil', 'icon' => 'user'],
        ];
    @endphp

    <input type="checkbox" id="sidebar-toggle" class="peer sr-only">

    <label for="sidebar-toggle" class="fixed left-4 top-4 z-50 inline-flex h-11 w-11 cursor-pointer items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 lg:hidden">
        @include('partials.icon', ['name' => 'menu', 'class' => 'h-5 w-5'])
        <span class="sr-only">Buka menu</span>
    </label>

    <aside class="fixed inset-y-0 left-0 z-40 flex w-72 -translate-x-full flex-col overflow-y-auto bg-blue-950 px-4 py-6 transition-transform duration-200 peer-checked:translate-x-0 lg:translate-x-0">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-2">
            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white shadow-lg shadow-blue-950/40">
                <img src="{{ asset('images/dompetku-mark.svg') }}" alt="Dompetku" class="h-9 w-9">
            </span>
            <span>
                <span class="block text-lg font-bold uppercase tracking-[0.14em] text-white">Dompetku</span>
                <span class="block text-[11px] text-slate-400">Personal expense tracker</span>
            </span>
        </a>

        <a href="{{ route('transactions.create') }}" class="btn btn-primary mt-6 w-full">
            @include('partials.icon', ['name' => 'plus', 'class' => 'h-4 w-4'])
            Catat Transaksi
        </a>

        <p class="mt-7 px-3 text-[11px] font-semibold uppercase tracking-widest text-slate-400">Menu</p>

        <nav class="mt-2 flex-1 space-y-1">
            @foreach ($navigation as $item)
                <a href="{{ route($item['route']) }}"
                   data-nav-link
                   class="nav-link relative {{ request()->routeIs($item['pattern']) ? 'nav-link-active' : '' }}">
                    @if (request()->routeIs($item['pattern']))
                        <span class="absolute left-0 top-1/2 h-5 w-1 -translate-y-1/2 rounded-r-full bg-cyan-400"></span>
                    @endif
                    @include('partials.icon', ['name' => $item['icon'], 'class' => 'h-5 w-5'])
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        <div class="mt-6 rounded-2xl bg-white/5 p-3 ring-1 ring-white/10">
            <div class="flex items-center gap-3">
                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-blue-500/20 text-sm font-semibold text-blue-200">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </span>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-medium text-white">{{ auth()->user()->name }}</p>
                    <p class="truncate text-xs text-slate-400">{{ auth()->user()->email }}</p>
                </div>
            </div>

            <div class="mt-3 flex items-center gap-2 border-t border-white/10 pt-3">
                <button type="button" data-theme-toggle class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg px-2 py-1.5 text-xs font-medium text-slate-300 transition hover:bg-white/10 hover:text-white" title="Ganti tema">
                    <span class="dark:hidden">@include('partials.icon', ['name' => 'moon', 'class' => 'h-4 w-4'])</span>
                    <span class="hidden dark:inline">@include('partials.icon', ['name' => 'sun', 'class' => 'h-4 w-4'])</span>
                    <span class="dark:hidden">Mode gelap</span>
                    <span class="hidden dark:inline">Mode terang</span>
                </button>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="rounded-lg p-1.5 text-slate-400 transition hover:bg-white/10 hover:text-white" title="Keluar">
                        @include('partials.icon', ['name' => 'logout', 'class' => 'h-5 w-5'])
                        <span class="sr-only">Keluar</span>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <div class="lg:pl-72">
        <main class="mx-auto w-full max-w-7xl px-4 pb-16 pt-20 sm:px-6 lg:px-8 lg:pt-8">
            @if (session('success'))
                <div class="mb-5 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-300">
                    <span class="mt-0.5 text-emerald-500">@include('partials.icon', ['name' => 'check', 'class' => 'h-5 w-5'])</span>
                    <p class="flex-1 font-medium">{{ session('success') }}</p>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-5 flex items-start gap-3 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 dark:border-rose-900/60 dark:bg-rose-950/40 dark:text-rose-300">
                    <span class="mt-0.5 text-rose-500">@include('partials.icon', ['name' => 'alert', 'class' => 'h-5 w-5'])</span>
                    <p class="flex-1 font-medium">{{ session('error') }}</p>
                </div>
            @endif

            @if ($errors->any() && ! View::hasSection('hide-error-summary'))
                <div class="mb-5 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:border-amber-900/60 dark:bg-amber-950/40 dark:text-amber-300">
                    <p class="font-semibold">Ada beberapa hal yang perlu diperbaiki:</p>
                    <ul class="mt-1 list-inside list-disc space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <script>
        document.querySelectorAll('[data-nav-link]').forEach(function (link) {
            link.addEventListener('click', function () {
                var toggle = document.getElementById('sidebar-toggle');
                if (toggle) {
                    toggle.checked = false;
                }
            });
        });
    </script>

    @stack('scripts')
</body>
</html>
