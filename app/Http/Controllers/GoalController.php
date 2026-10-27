<?php

namespace App\Http\Controllers;

use App\Models\Goal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class GoalController extends Controller
{
    public function index(): View
    {
        $goals = Goal::query()
            ->orderBy('target_date')
            ->orderBy('name')
            ->get();

        return view('goals.index', [
            'goals' => $goals,
            'totalTarget' => (float) $goals->sum('target_amount'),
            'totalSaved' => (float) $goals->sum('saved_amount'),
            'completedCount' => $goals->filter->is_completed->count(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateGoal($request);

        Goal::create($validated);

        return redirect()
            ->route('goals.index')
            ->with('success', 'Target tabungan berhasil dibuat.');
    }

    public function edit(Goal $goal): View
    {
        return view('goals.edit', [
            'goal' => $goal,
        ]);
    }

    public function update(Request $request, Goal $goal): RedirectResponse
    {
        $validated = $this->validateGoal($request);

        $goal->update($validated);

        return redirect()
            ->route('goals.index')
            ->with('success', 'Target tabungan berhasil diperbarui.');
    }

    public function destroy(Goal $goal): RedirectResponse
    {
        $goal->delete();

        return redirect()
            ->route('goals.index')
            ->with('success', 'Target tabungan berhasil dihapus.');
    }

    public function deposit(Request $request, Goal $goal): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
        ], [
            'amount.required' => 'Nominal setoran wajib diisi.',
            'amount.min' => 'Nominal setoran harus lebih dari nol.',
        ]);

        $amount = (float) $validated['amount'];

        if ($amount > $goal->remaining) {
            throw ValidationException::withMessages([
                'amount' => 'Setoran melebihi sisa target. Sisa yang dibutuhkan: '.rupiah($goal->remaining).'.',
            ]);
        }

        $goal->update([
            'saved_amount' => (float) $goal->saved_amount + $amount,
        ]);

        return redirect()
            ->route('goals.index')
            ->with('success', 'Setoran '.rupiah($amount).' berhasil dicatat untuk "'.$goal->name.'".');
    }

    private function validateGoal(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'target_amount' => ['required', 'numeric', 'min:1', 'max:999999999999'],
            'target_date' => ['nullable', 'date'],
            'color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ], [
            'name.required' => 'Nama target wajib diisi.',
            'name.max' => 'Nama target maksimal 80 karakter.',
            'target_amount.required' => 'Nominal target wajib diisi.',
            'target_amount.min' => 'Nominal target harus lebih dari nol.',
            'target_date.date' => 'Format tanggal tidak valid.',
            'color.regex' => 'Format warna tidak valid.',
        ]);
    }
}
