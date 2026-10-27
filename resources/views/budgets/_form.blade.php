<div class="space-y-4">
    <div>
        <label for="category_id" class="label">Kategori pengeluaran</label>
        <select id="category_id" name="category_id" class="input">
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((string) old('category_id', $budget->category_id ?? '') === (string) $category->id)>
                    {{ $category->icon }} {{ $category->name }}
                </option>
            @endforeach
        </select>
        @error('category_id')
            <p class="mt-1.5 text-sm text-rose-700 dark:text-rose-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="month" class="label">Bulan</label>
        <input id="month" name="month" type="month" value="{{ old('month', isset($budget) ? $budget->month->format('Y-m') : now()->format('Y-m')) }}" required class="input">
        @error('month')
            <p class="mt-1.5 text-sm text-rose-700 dark:text-rose-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="amount" class="label">Batas budget</label>
        <div class="relative">
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-sm font-medium text-slate-500 dark:text-slate-400">Rp</span>
            <input id="amount" name="amount" type="number" min="1" step="1000" value="{{ old('amount', isset($budget) ? (float) $budget->amount : '') }}" required class="input pl-10" placeholder="0">
        </div>
        @error('amount')
            <p class="mt-1.5 text-sm text-rose-700 dark:text-rose-400">{{ $message }}</p>
        @enderror
    </div>
</div>
