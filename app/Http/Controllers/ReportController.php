<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesPeriod;
use App\Models\Transaction;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    use ResolvesPeriod;

    public function index(Request $request): View
    {
        $month = $this->resolveMonth($request->query('month'));

        return view('reports.index', $this->reportData($month) + [
            'month' => $month,
            'monthOptions' => $this->monthOptions(),
        ]);
    }

    public function pdf(Request $request): Response
    {
        $month = $this->resolveMonth($request->query('month'));

        $fontPath = storage_path('fonts');
        File::ensureDirectoryExists($fontPath);

        $options = new Options;
        $options->setDefaultFont('DejaVu Sans');
        $options->setFontCache($fontPath);
        $options->setTempDir($fontPath);
        $options->setChroot(base_path());

        $dompdf = new Dompdf($options);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->loadHtml(view('reports.pdf', $this->reportData($month) + [
            'month' => $month,
            'user' => $request->user(),
        ])->render());
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="laporan-'.$month->format('Y-m').'.pdf"',
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $month = $this->resolveMonth($request->query('month'));
        $transactions = $this->reportData($month)['transactions'];

        $filename = 'laporan-'.$month->format('Y-m').'.csv';

        return response()->streamDownload(function () use ($transactions) {
            $handle = fopen('php://output', 'w');

            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Tanggal', 'Jenis', 'Kategori', 'Dompet', 'Catatan', 'Nominal']);

            foreach ($transactions as $transaction) {
                fputcsv($handle, [
                    $transaction->date->format('Y-m-d'),
                    $transaction->type === 'income' ? 'Pemasukan' : 'Pengeluaran',
                    $transaction->category->name ?? '-',
                    $transaction->wallet->name ?? '-',
                    $transaction->note ?? '-',
                    number_format((float) $transaction->amount, 2, '.', ''),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function reportData(Carbon $month): array
    {
        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();

        $transactions = Transaction::with(['category', 'wallet'])
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('date')
            ->get();

        $income = (float) $transactions->where('type', 'income')->sum('amount');
        $expense = (float) $transactions->where('type', 'expense')->sum('amount');

        $previousMonth = $start->copy()->subMonth();
        $previousTransactions = Transaction::query()
            ->whereYear('date', $previousMonth->year)
            ->whereMonth('date', $previousMonth->month)
            ->get(['type', 'amount']);

        $previousExpense = (float) $previousTransactions->where('type', 'expense')->sum('amount');
        $expenseDelta = $previousExpense > 0
            ? round(($expense - $previousExpense) / $previousExpense * 100, 1)
            : null;

        $expenseByCategory = $transactions->where('type', 'expense')
            ->groupBy('category_id')
            ->map(fn ($items) => [
                'name' => $items->first()->category->name ?? 'Tanpa kategori',
                'color' => $items->first()->category->color ?? '#94a3b8',
                'icon' => $items->first()->category->icon ?? '📦',
                'total' => (float) $items->sum('amount'),
            ])
            ->sortByDesc('total')
            ->values();

        $incomeByCategory = $transactions->where('type', 'income')
            ->groupBy('category_id')
            ->map(fn ($items) => [
                'name' => $items->first()->category->name ?? 'Tanpa kategori',
                'color' => $items->first()->category->color ?? '#94a3b8',
                'total' => (float) $items->sum('amount'),
            ])
            ->sortByDesc('total')
            ->values();

        $byWallet = $transactions->groupBy('wallet_id')
            ->map(fn ($items) => [
                'name' => $items->first()->wallet->name ?? 'Dompet terhapus',
                'color' => $items->first()->wallet->color ?? '#94a3b8',
                'income' => (float) $items->where('type', 'income')->sum('amount'),
                'expense' => (float) $items->where('type', 'expense')->sum('amount'),
            ])
            ->values();

        $daily = collect(range(1, $start->daysInMonth))->map(function (int $day) use ($transactions) {
            $dayTransactions = $transactions->filter(fn (Transaction $transaction) => (int) $transaction->date->day === $day);

            return [
                'day' => $day,
                'income' => (float) $dayTransactions->where('type', 'income')->sum('amount'),
                'expense' => (float) $dayTransactions->where('type', 'expense')->sum('amount'),
            ];
        });

        $biggestExpense = $transactions->where('type', 'expense')->sortByDesc('amount')->first();

        return [
            'transactions' => $transactions,
            'income' => $income,
            'expense' => $expense,
            'expenseDelta' => $expenseDelta,
            'expenseByCategory' => $expenseByCategory,
            'incomeByCategory' => $incomeByCategory,
            'byWallet' => $byWallet,
            'daily' => $daily,
            'biggestExpense' => $biggestExpense,
        ];
    }
}
