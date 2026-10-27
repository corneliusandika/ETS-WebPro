@extends('layouts.guest')

@section('title', 'Masuk')

@section('content')
    <div class="card bg-white/90 shadow-xl shadow-slate-900/5 backdrop-blur-xl dark:bg-slate-800/80 dark:shadow-black/30">
        <div class="card-body">
            <h1 class="title-page">Masuk ke akunmu</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Lanjutkan mencatat dan memantau keuanganmu.</p>

            <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
                @csrf

                <div>
                    <label for="email" class="label">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="input" placeholder="nama@email.com">
                    @error('email')
                        <p class="mt-1.5 text-sm text-rose-700 dark:text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="label">Password</label>
                    <input id="password" name="password" type="password" required autocomplete="current-password" class="input" placeholder="••••••••">
                    @error('password')
                        <p class="mt-1.5 text-sm text-rose-700 dark:text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                    <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-900">
                    Ingat saya
                </label>

                <button type="submit" class="btn btn-primary w-full">Masuk</button>
            </form>

            <p class="mt-6 text-center text-sm text-slate-500 dark:text-slate-400">
                Belum punya akun?
                <a href="{{ route('register') }}" class="font-semibold text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300">Daftar sekarang</a>
            </p>
        </div>
    </div>
@endsection
