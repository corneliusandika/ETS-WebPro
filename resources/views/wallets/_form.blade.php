@php
    $selectedColor = old('color', $wallet->color ?? '#0ea5e9');
@endphp

<div class="space-y-4">
    <div>
        <label for="name" class="label">Nama dompet</label>
        <input id="name" name="name" type="text" value="{{ old('name', $wallet->name ?? '') }}" required maxlength="60" class="input" placeholder="Contoh: Dompet Tunai">
        @error('name')
            <p class="mt-1.5 text-sm text-rose-700 dark:text-rose-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="type" class="label">Jenis</label>
        <select id="type" name="type" class="input">
            @foreach (\App\Models\Wallet::TYPES as $value => $label)
                <option value="{{ $value }}" @selected(old('type', $wallet->type ?? 'cash') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('type')
            <p class="mt-1.5 text-sm text-rose-700 dark:text-rose-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="initial_balance" class="label">Saldo awal</label>
        <div class="relative">
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-sm font-medium text-slate-500 dark:text-slate-400">Rp</span>
            <input id="initial_balance" name="initial_balance" type="number" min="0" step="100" value="{{ old('initial_balance', isset($wallet) ? (float) $wallet->initial_balance : 0) }}" required class="input pl-10">
        </div>
        @error('initial_balance')
            <p class="mt-1.5 text-sm text-rose-700 dark:text-rose-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <span class="label">Warna</span>
        <div class="flex flex-wrap items-center gap-2 py-1.5">
            @foreach (\App\Models\Wallet::COLORS as $color)
                <label class="cursor-pointer">
                    <input type="radio" name="color" value="{{ $color }}" class="peer sr-only" @checked($selectedColor === $color)>
                    <span class="block h-7 w-7 rounded-full ring-offset-2 transition peer-checked:ring-2 peer-checked:ring-slate-900 dark:ring-offset-slate-900 dark:peer-checked:ring-white" style="background-color: {{ $color }}"></span>
                </label>
            @endforeach
        </div>
        @error('color')
            <p class="mt-1.5 text-sm text-rose-700 dark:text-rose-400">{{ $message }}</p>
        @enderror
    </div>

    <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
        <input type="checkbox" name="is_active" value="1" class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-900" @checked(old('is_active', $wallet->is_active ?? true))>
        Dompet aktif
    </label>
</div>
