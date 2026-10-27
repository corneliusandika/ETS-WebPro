<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesPeriod;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BudgetController extends Controller
{
    use ResolvesPeriod;

    public function index(Request $request): View
    {
        $month = $this->resolveMonth($request->query('month'));
        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();

        $budgets = Budget::with('category')
            ->whereYear('month', $start->year)
            ->whereMonth('month', $start->month)
            ->orderByDesc('amount')
            ->get();

        $spentMap = Transaction::query()
            ->where('type', 'expense')
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->groupBy('category_id')
            ->selectRaw('category_id, SUM(amount) as total')
            ->pluck('total', 'category_id');

        return view('budgets.index', [
            'budgets' => $budgets,
            'month' => $month,
            'monthOptions' => $this->monthOptions(),
            'spentMap' => $spentMap,
            'totalBudget' => (float) $budgets->sum('amount'),
            'totalSpent' => (float) $spentMap->sum(),
            'categories' => Category::expense()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateBudget($request);
        $validated['month'] = $this->normalizeMonth($validated['month']);

        Budget::create($validated);

        return redirect()
            ->route('budgets.index', ['month' => substr($validated['month'], 0, 7)])
            ->with('success', 'Budget berhasil ditambahkan.');
    }

    public function edit(Budget $budget): View
    {
        return view('budgets.edit', [
            'budget' => $budget,
            'categories' => Category::expense()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Budget $budget): RedirectResponse
    {
        $validated = $this->validateBudget($request, $budget);
        $validated['month'] = $this->normalizeMonth($validated['month']);

        $budget->update($validated);

        return redirect()
            ->route('budgets.index', ['month' => substr($validated['month'], 0, 7)])
            ->with('success', 'Budget berhasil diperbarui.');
    }

    public function destroy(Budget $budget): RedirectResponse
    {
        $budget->delete();

        return redirect()
            ->route('budgets.index')
            ->with('success', 'Budget berhasil dihapus.');
    }

    private function validateBudget(Request $request, ?Budget $budget = null): array
    {
        $validated = $request->validate([
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')
                    ->where('user_id', $request->user()->id)
                    ->where('type', 'expense'),
                Rule::unique('budgets', 'category_id')
                    ->where('user_id', $request->user()->id)
                    ->where(fn ($query) => $query->whereDate('month', $this->normalizeMonth($request->input('month'))))
                    ->ignore($budget?->id),
            ],
            'month' => ['required', 'date_format:Y-m'],
            'amount' => ['required', 'numeric', 'min:1', 'max:999999999999'],
        ], [
            'category_id.required' => 'Kategori wajib dipilih.',
            'category_id.unique' => 'Budget untuk kategori ini sudah ada di bulan tersebut.',
            'month.required' => 'Bulan wajib dipilih.',
            'month.date_format' => 'Format bulan tidak valid.',
            'amount.required' => 'Nominal budget wajib diisi.',
            'amount.min' => 'Nominal budget harus lebih dari nol.',
        ]);

        $category = Category::find($validated['category_id']);

        if ($category && $category->type !== 'expense') {
            throw ValidationException::withMessages([
                'category_id' => 'Budget hanya bisa dibuat untuk kategori pengeluaran.',
            ]);
        }

        return $validated;
    }

    private function normalizeMonth(?string $month): string
    {
        if (! $month || ! preg_match('/^\d{4}-\d{2}$/', $month)) {
            return now()->startOfMonth()->toDateString();
        }

        return substr($month, 0, 7).'-01';
    }
}
