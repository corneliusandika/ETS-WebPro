<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::withCount('transactions')
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        return view('categories.index', [
            'categories' => $categories,
            'incomeCategories' => $categories->where('type', 'income'),
            'expenseCategories' => $categories->where('type', 'expense'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateCategory($request);

        Category::create($validated);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Kategori "'.$validated['name'].'" berhasil ditambahkan.');
    }

    public function edit(Category $category): View
    {
        return view('categories.edit', [
            'category' => $category,
        ]);
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $this->validateCategory($request, $category);

        $category->update($validated);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->transactions()->exists() || $category->budgets()->exists()) {
            return redirect()
                ->route('categories.index')
                ->with('error', 'Kategori tidak bisa dihapus karena masih dipakai pada transaksi atau budget.');
        }

        $category->delete();

        return redirect()
            ->route('categories.index')
            ->with('success', 'Kategori berhasil dihapus.');
    }

    private function validateCategory(Request $request, ?Category $category = null): array
    {
        $validated = $request->validate([
            'name' => [
                'required', 'string', 'max:60',
                Rule::unique('categories', 'name')
                    ->where('user_id', $request->user()->id)
                    ->where('type', $request->input('type'))
                    ->ignore($category?->id),
            ],
            'type' => ['required', Rule::in(['income', 'expense'])],
            'color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'icon' => ['required', 'string', 'max:8'],
        ], [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.unique' => 'Kategori dengan nama dan jenis tersebut sudah ada.',
            'name.max' => 'Nama kategori maksimal 60 karakter.',
            'type.required' => 'Jenis kategori wajib dipilih.',
            'color.regex' => 'Format warna tidak valid.',
            'icon.required' => 'Ikon kategori wajib dipilih.',
        ]);

        return $validated;
    }
}
