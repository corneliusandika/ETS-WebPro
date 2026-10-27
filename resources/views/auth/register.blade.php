@extends('layouts.guest')

@section('title', 'Daftar')

@section('content')
    <div class="card bg-white/90 shadow-xl shadow-slate-900/5 backdrop-blur-xl dark:bg-slate-800/80 dark:shadow-black/30">
        <div class="card-body">
            <h1 class="title-page">Buat akun baru</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Gratis, dan kategori default langsung tersedia.</p>

            <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-4">
                @csrf

                <div>
                    <label for="name" class="label">Nama lengkap</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus autocomplete="name" class="input" placeholder="Nama kamu">
                    @error('name')
                        <p class="mt-1.5 text-sm text-rose-700 dark:text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="label">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="username" class="input" placeholder="nama@email.com">
                    @error('email')
                        <p class="mt-1.5 text-sm text-rose-700 dark:text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="label">Password</label>
                    <input id="password" name="password" type="password" required autocomplete="new-password" class="input" placeholder="Minimal 8 karakter">
                    <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">Minimal 8 karakter, mengandung huruf besar &amp; kecil, angka, dan simbol.</p>
                    @error('password')
                        <p class="mt-1.5 text-sm text-rose-700 dark:text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="label">Konfirmasi password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="input" placeholder="Ulangi password">
                </div>

                <button type="submit" class="btn btn-primary w-full">Daftar</button>
            </form>

            <p class="mt-6 text-center text-sm text-slate-500 dark:text-slate-400">
                Sudah punya akun?
                <a href="{{ route('login') }}" class="font-semibold text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300">Masuk di sini</a>
            </p>
        </div>
    </div>
@endsection
