<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesPeriod;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TransactionController extends Controller
{
    use ResolvesPeriod;

    public function index(Request $request): View
    {
        $filters = [
            'month' => $request->query('month', now()->format('Y-m')),
            'type' => $request->query('type'),
            'category_id' => $request->query('category_id'),
            'wallet_id' => $request->query('wallet_id'),
            'q' => $request->query('q'),
        ];

        $query = Transaction::with(['category', 'wallet'])
            ->when($filters['month'], fn ($query) => $query->inMonth($filters['month']))
            ->when(in_array($filters['type'], ['income', 'expense'], true), fn ($query) => $query->where('type', $filters['type']))
            ->when($filters['category_id'], fn ($query) => $query->where('category_id', $filters['category_id']))
            ->when($filters['wallet_id'], fn ($query) => $query->where('wallet_id', $filters['wallet_id']))
            ->when($filters['q'], fn ($query) => $query->where(function ($query) use ($filters) {
                $query->where('note', 'like', '%'.$filters['q'].'%');
            }));

        $totals = (clone $query)->reorder()->get(['type', 'amount']);

        return view('transactions.index', [
            'transactions' => $query->orderByDesc('date')->orderByDesc('id')->paginate(10)->withQueryString(),
            'filters' => $filters,
            'monthOptions' => $this->monthOptions(),
            'categories' => Category::orderBy('type')->orderBy('name')->get(),
            'wallets' => Wallet::orderBy('name')->get(),
            'totalIncome' => (float) $totals->where('type', 'income')->sum('amount'),
            'totalExpense' => (float) $totals->where('type', 'expense')->sum('amount'),
        ]);
    }

    public function create(): View|RedirectResponse
    {
        if (Wallet::count() === 0 || Category::count() === 0) {
            return redirect()
                ->route('wallets.index')
                ->with('error', 'Tambahkan minimal satu dompet dan satu kategori sebelum mencatat transaksi.');
        }

        return view('transactions.create', [
            'categories' => Category::orderBy('type')->orderBy('name')->get(),
            'wallets' => Wallet::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateTransaction($request);
        $validated['note'] = $validated['note'] ?? null;

        Transaction::create($validated);

        return redirect()
            ->route('transactions.index')
            ->with('success', 'Transaksi berhasil ditambahkan.');
    }

    public function edit(Transaction $transaction): View
    {
        return view('transactions.edit', [
            'transaction' => $transaction,
            'categories' => Category::orderBy('type')->orderBy('name')->get(),
            'wallets' => Wallet::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Transaction $transaction): RedirectResponse
    {
        $validated = $this->validateTransaction($request);
        $validated['note'] = $validated['note'] ?? null;

        $transaction->update($validated);

        return redirect()
            ->route('transactions.index')
            ->with('success', 'Transaksi berhasil diperbarui.');
    }

    public function destroy(Transaction $transaction): RedirectResponse
    {
        $transaction->delete();

        return redirect()
            ->route('transactions.index')
            ->with('success', 'Transaksi berhasil dihapus.');
    }

    private function validateTransaction(Request $request): array
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in(['income', 'expense'])],
            'amount' => ['required', 'numeric', 'min:1', 'max:999999999999'],
            'date' => ['required', 'date'],
            'wallet_id' => ['required', Rule::exists('wallets', 'id')->where('user_id', $request->user()->id)],
            'category_id' => ['required', Rule::exists('categories', 'id')->where('user_id', $request->user()->id)],
            'note' => ['nullable', 'string', 'max:255'],
        ], [
            'type.required' => 'Jenis transaksi wajib dipilih.',
            'amount.required' => 'Nominal wajib diisi.',
            'amount.numeric' => 'Nominal harus berupa angka.',
            'amount.min' => 'Nominal harus lebih dari nol.',
            'date.required' => 'Tanggal wajib diisi.',
            'date.date' => 'Format tanggal tidak valid.',
            'wallet_id.required' => 'Dompet wajib dipilih.',
            'category_id.required' => 'Kategori wajib dipilih.',
            'note.max' => 'Catatan maksimal 255 karakter.',
        ]);

        $category = Category::find($validated['category_id']);

        if ($category && $category->type !== $validated['type']) {
            throw ValidationException::withMessages([
                'category_id' => 'Kategori yang dipilih tidak sesuai dengan jenis transaksi.',
            ]);
        }

        $validated['amount'] = round((float) $validated['amount'], 2);

        return $validated;
    }
}
