@php
    $selectedType = old('type', $transaction->type ?? 'expense');
    $selectedWallet = old('wallet_id', $transaction->wallet_id ?? $wallets->first()->id ?? '');
    $selectedCategory = old('category_id', $transaction->category_id ?? '');
@endphp

<div class="space-y-5">
    <div>
        <span class="label">Jenis transaksi</span>
        <div class="grid grid-cols-2 gap-3">
            <label class="cursor-pointer">
                <input type="radio" name="type" value="expense" class="peer sr-only" @checked($selectedType === 'expense')>
                <span class="flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-600 transition peer-checked:border-rose-500 peer-checked:bg-rose-50 peer-checked:text-rose-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:peer-checked:border-rose-500 dark:peer-checked:bg-rose-950/40 dark:peer-checked:text-rose-400">
                    @include('partials.icon', ['name' => 'arrow-up', 'class' => 'h-4 w-4'])
                    Pengeluaran
                </span>
            </label>
            <label class="cursor-pointer">
                <input type="radio" name="type" value="income" class="peer sr-only" @checked($selectedType === 'income')>
                <span class="flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-600 transition peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:peer-checked:border-emerald-500 dark:peer-checked:bg-emerald-950/40 dark:peer-checked:text-emerald-400">
                    @include('partials.icon', ['name' => 'arrow-down', 'class' => 'h-4 w-4'])
                    Pemasukan
                </span>
            </label>
        </div>
        @error('type')
            <p class="mt-1.5 text-sm text-rose-700 dark:text-rose-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="amount" class="label">Nominal</label>
        <div class="relative">
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-sm font-medium text-slate-500 dark:text-slate-400">Rp</span>
            <input id="amount" name="amount" type="number" min="1" step="100" value="{{ old('amount', isset($transaction) ? (float) $transaction->amount : '') }}" required class="input pl-10" placeholder="0">
        </div>
        @error('amount')
            <p class="mt-1.5 text-sm text-rose-700 dark:text-rose-400">{{ $message }}</p>
        @enderror
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
            <label for="date" class="label">Tanggal</label>
            <input id="date" name="date" type="date" value="{{ old('date', isset($transaction) ? $transaction->date->format('Y-m-d') : now()->toDateString()) }}" required class="input">
            @error('date')
                <p class="mt-1.5 text-sm text-rose-700 dark:text-rose-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="wallet_id" class="label">Dompet</label>
            <select id="wallet_id" name="wallet_id" required class="input">
                @foreach ($wallets as $wallet)
                    <option value="{{ $wallet->id }}" @selected((string) $selectedWallet === (string) $wallet->id)>
                        {{ $wallet->name }} ({{ $wallet->type_label }})
                    </option>
                @endforeach
            </select>
            @error('wallet_id')
                <p class="mt-1.5 text-sm text-rose-700 dark:text-rose-400">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div>
        <label for="category_id" class="label">Kategori</label>
        <select id="category_id" name="category_id" required class="input">
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" data-type="{{ $category->type }}" @selected((string) $selectedCategory === (string) $category->id)>
                    {{ $category->icon }} {{ $category->name }} — {{ $category->type === 'income' ? 'Pemasukan' : 'Pengeluaran' }}
                </option>
            @endforeach
        </select>
        @error('category_id')
            <p class="mt-1.5 text-sm text-rose-700 dark:text-rose-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="note" class="label">Catatan <span class="font-normal text-slate-500 dark:text-slate-400">(opsional)</span></label>
        <textarea id="note" name="note" rows="3" maxlength="255" class="input" placeholder="Contoh: makan siang di kantin">{{ old('note', $transaction->note ?? '') }}</textarea>
        @error('note')
            <p class="mt-1.5 text-sm text-rose-700 dark:text-rose-400">{{ $message }}</p>
        @enderror
    </div>
</div>

@push('scripts')
    <script>
        (function () {
            const typeInputs = document.querySelectorAll('input[name="type"]');
            const categorySelect = document.getElementById('category_id');

            function syncCategories() {
                const type = document.querySelector('input[name="type"]:checked')?.value;
                if (!type || !categorySelect) {
                    return;
                }

                let firstVisible = null;

                categorySelect.querySelectorAll('option').forEach(function (option) {
                    const matches = option.dataset.type === type;
                    option.hidden = !matches;
                    option.disabled = !matches;

                    if (matches && !firstVisible) {
                        firstVisible = option;
                    }
                });

                const current = categorySelect.options[categorySelect.selectedIndex];
                if (!current || current.disabled) {
                    categorySelect.value = firstVisible ? firstVisible.value : '';
                }
            }

            typeInputs.forEach(function (input) {
                input.addEventListener('change', syncCategories);
            });

            syncCategories();
        })();
    </script>
@endpush
