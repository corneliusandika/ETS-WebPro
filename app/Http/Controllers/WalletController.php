<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WalletController extends Controller
{
    public function index(): View
    {
        $wallets = Wallet::query()
            ->withSum(['transactions as income_total' => fn ($query) => $query->where('type', 'income')], 'amount')
            ->withSum(['transactions as expense_total' => fn ($query) => $query->where('type', 'expense')], 'amount')
            ->withCount('transactions')
            ->orderBy('name')
            ->get();

        return view('wallets.index', [
            'wallets' => $wallets,
            'totalBalance' => $wallets->sum(fn (Wallet $wallet) => $wallet->balance),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateWallet($request);

        Wallet::create($validated);

        return redirect()
            ->route('wallets.index')
            ->with('success', 'Dompet "'.$validated['name'].'" berhasil ditambahkan.');
    }

    public function edit(Wallet $wallet): View
    {
        return view('wallets.edit', [
            'wallet' => $wallet,
        ]);
    }

    public function update(Request $request, Wallet $wallet): RedirectResponse
    {
        $validated = $this->validateWallet($request, $wallet);
        $validated['is_active'] = $request->boolean('is_active');

        $wallet->update($validated);

        return redirect()
            ->route('wallets.index')
            ->with('success', 'Dompet berhasil diperbarui.');
    }

    public function destroy(Wallet $wallet): RedirectResponse
    {
        if ($wallet->transactions()->exists()) {
            return redirect()
                ->route('wallets.index')
                ->with('error', 'Dompet tidak bisa dihapus karena masih memiliki transaksi. Pindahkan atau hapus transaksinya terlebih dahulu.');
        }

        $wallet->delete();

        return redirect()
            ->route('wallets.index')
            ->with('success', 'Dompet berhasil dihapus.');
    }

    private function validateWallet(Request $request, ?Wallet $wallet = null): array
    {
        return $request->validate([
            'name' => [
                'required', 'string', 'max:60',
                Rule::unique('wallets', 'name')
                    ->where('user_id', $request->user()->id)
                    ->ignore($wallet?->id),
            ],
            'type' => ['required', Rule::in(array_keys(Wallet::TYPES))],
            'initial_balance' => ['required', 'numeric', 'min:0', 'max:999999999999'],
            'color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ], [
            'name.required' => 'Nama dompet wajib diisi.',
            'name.unique' => 'Kamu sudah punya dompet dengan nama tersebut.',
            'name.max' => 'Nama dompet maksimal 60 karakter.',
            'type.required' => 'Jenis dompet wajib dipilih.',
            'initial_balance.required' => 'Saldo awal wajib diisi.',
            'initial_balance.min' => 'Saldo awal tidak boleh negatif.',
            'initial_balance.numeric' => 'Saldo awal harus berupa angka.',
            'color.regex' => 'Format warna tidak valid.',
        ]);
    }
}
