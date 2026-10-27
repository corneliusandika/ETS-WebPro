@php
    $selectedType = old('type', $category->type ?? 'expense');
    $selectedColor = old('color', $category->color ?? '#6366f1');
    $selectedIcon = old('icon', $category->icon ?? '📦');
@endphp

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <label for="name" class="label">Nama kategori</label>
        <input id="name" name="name" type="text" value="{{ old('name', $category->name ?? '') }}" required maxlength="60" class="input" placeholder="Contoh: Makan & Minum">
        @error('name')
            <p class="mt-1.5 text-sm text-rose-700 dark:text-rose-400">{{ $message }}</p>
        @enderror
    </div>

    <div class="sm:col-span-2">
        <span class="label">Jenis</span>
        <div class="grid grid-cols-2 gap-3">
            <label class="cursor-pointer">
                <input type="radio" name="type" value="expense" class="peer sr-only" @checked($selectedType === 'expense')>
                <span class="flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition peer-checked:border-rose-500 peer-checked:bg-rose-50 peer-checked:text-rose-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:peer-checked:border-rose-500 dark:peer-checked:bg-rose-950/40 dark:peer-checked:text-rose-400">
                    Pengeluaran
                </span>
            </label>
            <label class="cursor-pointer">
                <input type="radio" name="type" value="income" class="peer sr-only" @checked($selectedType === 'income')>
                <span class="flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:peer-checked:border-emerald-500 dark:peer-checked:bg-emerald-950/40 dark:peer-checked:text-emerald-400">
                    Pemasukan
                </span>
            </label>
        </div>
        @error('type')
            <p class="mt-1.5 text-sm text-rose-700 dark:text-rose-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="icon" class="label">Ikon</label>
        <select id="icon" name="icon" class="input">
            @foreach (\App\Models\Category::ICONS as $icon)
                <option value="{{ $icon }}" @selected($selectedIcon === $icon)>{{ $icon }}</option>
            @endforeach
        </select>
        @error('icon')
            <p class="mt-1.5 text-sm text-rose-700 dark:text-rose-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <span class="label">Warna</span>
        <div class="flex flex-wrap items-center gap-2 py-1.5">
            @foreach (\App\Models\Category::COLORS as $color)
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
</div>
