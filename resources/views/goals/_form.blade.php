@php
    $selectedColor = old('color', $goal->color ?? '#8b5cf6');
@endphp

<div class="space-y-4">
    <div>
        <label for="name" class="label">Nama target</label>
        <input id="name" name="name" type="text" value="{{ old('name', $goal->name ?? '') }}" required maxlength="80" class="input" placeholder="Contoh: Dana darurat">
        @error('name')
            <p class="mt-1.5 text-sm text-rose-700 dark:text-rose-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="target_amount" class="label">Nominal target</label>
        <div class="relative">
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-sm font-medium text-slate-500 dark:text-slate-400">Rp</span>
            <input id="target_amount" name="target_amount" type="number" min="1" step="1000" value="{{ old('target_amount', isset($goal) ? (float) $goal->target_amount : '') }}" required class="input pl-10" placeholder="0">
        </div>
        @error('target_amount')
            <p class="mt-1.5 text-sm text-rose-700 dark:text-rose-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="target_date" class="label">Target tanggal <span class="font-normal text-slate-500 dark:text-slate-400">(opsional)</span></label>
        <input id="target_date" name="target_date" type="date" value="{{ old('target_date', isset($goal) && $goal->target_date ? $goal->target_date->format('Y-m-d') : '') }}" class="input">
        @error('target_date')
            <p class="mt-1.5 text-sm text-rose-700 dark:text-rose-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <span class="label">Warna</span>
        <div class="flex flex-wrap items-center gap-2 py-1.5">
            @foreach (['#8b5cf6', '#6366f1', '#0ea5e9', '#10b981', '#f59e0b', '#ec4899', '#f43f5e'] as $color)
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
