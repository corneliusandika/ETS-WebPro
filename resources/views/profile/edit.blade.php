@extends('layouts.app')

@section('title', 'Profil')

@section('content')
    <div>
        <h1 class="title-page">Profil & Pengaturan</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Kelola informasi akun dan keamanan login kamu.</p>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="card card-body h-fit lg:col-span-1">
            <div class="flex items-center gap-3">
                <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-sky-600 text-xl font-bold text-white">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </span>
                <div class="min-w-0">
                    <p class="truncate text-base font-semibold text-slate-900 dark:text-white">{{ $user->name }}</p>
                    <p class="truncate text-sm text-slate-500 dark:text-slate-400">{{ $user->email }}</p>
                </div>
            </div>

            <dl class="mt-6 space-y-3 text-sm">
                <div class="flex items-center justify-between">
                    <dt class="text-slate-500 dark:text-slate-400">Bergabung sejak</dt>
                    <dd class="font-medium text-slate-800 dark:text-slate-200">{{ tanggal_indonesia($user->created_at) }}</dd>
                </div>
                <div class="flex items-center justify-between">
                    <dt class="text-slate-500 dark:text-slate-400">Jumlah dompet</dt>
                    <dd class="font-medium text-slate-800 dark:text-slate-200">{{ $user->wallets()->count() }}</dd>
                </div>
                <div class="flex items-center justify-between">
                    <dt class="text-slate-500 dark:text-slate-400">Jumlah kategori</dt>
                    <dd class="font-medium text-slate-800 dark:text-slate-200">{{ $user->categories()->count() }}</dd>
                </div>
                <div class="flex items-center justify-between">
                    <dt class="text-slate-500 dark:text-slate-400">Total transaksi</dt>
                    <dd class="font-medium text-slate-800 dark:text-slate-200">{{ $user->transactions()->count() }}</dd>
                </div>
            </dl>

            <form method="POST" action="{{ route('logout') }}" class="mt-6">
                @csrf
                <button type="submit" class="btn btn-secondary w-full">
                    @include('partials.icon', ['name' => 'logout', 'class' => 'h-4 w-4'])
                    Keluar dari akun
                </button>
            </form>
        </div>

        <div class="space-y-6 lg:col-span-2">
            <form method="POST" action="{{ route('profile.update') }}" class="card card-body">
                @csrf
                @method('PATCH')

                <h2 class="text-base font-semibold text-slate-900 dark:text-white">Informasi akun</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Perbarui nama dan email yang dipakai untuk login.</p>

                <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="name" class="label">Nama lengkap</label>
                        <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required class="input">
                        @error('name')
                            <p class="mt-1.5 text-sm text-rose-700 dark:text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="email" class="label">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required class="input">
                        @error('email')
                            <p class="mt-1.5 text-sm text-rose-700 dark:text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <button type="submit" class="btn btn-primary mt-5">Simpan perubahan</button>
            </form>

            <form method="POST" action="{{ route('profile.password') }}" class="card card-body">
                @csrf
                @method('PUT')

                <h2 class="text-base font-semibold text-slate-900 dark:text-white">Ubah password</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Gunakan password yang kuat dan tidak dipakai di layanan lain.</p>

                <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <label for="current_password" class="label">Password saat ini</label>
                        <input id="current_password" name="current_password" type="password" required class="input" autocomplete="current-password">
                        @error('current_password')
                            <p class="mt-1.5 text-sm text-rose-700 dark:text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="password" class="label">Password baru</label>
                        <input id="password" name="password" type="password" required class="input" autocomplete="new-password">
                        @error('password')
                            <p class="mt-1.5 text-sm text-rose-700 dark:text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="password_confirmation" class="label">Konfirmasi</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required class="input" autocomplete="new-password">
                    </div>
                </div>

                <p class="mt-3 text-xs text-slate-500 dark:text-slate-400">
                    Password minimal 8 karakter, mengandung huruf besar &amp; kecil, angka, dan simbol.
                </p>

                <button type="submit" class="btn btn-primary mt-5">Perbarui password</button>
            </form>
        </div>
    </div>
@endsection
