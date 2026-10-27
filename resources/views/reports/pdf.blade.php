<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan {{ nama_bulan($month) }} — {{ config('app.name', 'Dompetku') }}</title>
    <style>
        @page {
            margin: 32px 36px 42px 36px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #1e293b;
            margin: 0;
        }

        .header {
            width: 100%;
            border-collapse: collapse;
        }

        .brand {
            font-size: 17px;
            font-weight: bold;
            color: #4338ca;
            letter-spacing: 0.5px;
        }

        .brand small {
            display: block;
            font-size: 8px;
            font-weight: normal;
            color: #64748b;
            letter-spacing: 0;
            margin-top: 2px;
        }

        .doc-title {
            text-align: right;
            font-size: 14px;
            font-weight: bold;
            color: #0f172a;
        }

        .doc-subtitle {
            text-align: right;
            font-size: 9px;
            color: #64748b;
            margin-top: 2px;
        }

        .divider {
            border-top: 2px solid #4f46e5;
            margin: 12px 0 14px 0;
        }

        .meta {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }

        .meta td {
            font-size: 9px;
            color: #475569;
            padding: 1px 0;
        }

        .meta .value {
            color: #0f172a;
            font-weight: bold;
        }

        .summary {
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px 0;
            margin-bottom: 16px;
        }

        .summary td {
            width: 25%;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 9px 10px;
            vertical-align: top;
        }

        .summary .label {
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: #64748b;
        }

        .summary .amount {
            font-size: 13px;
            font-weight: bold;
            margin-top: 4px;
        }

        .income {
            color: #047857;
        }

        .expense {
            color: #be123c;
        }

        .neutral {
            color: #0f172a;
        }

        .note {
            font-size: 8px;
            color: #64748b;
            margin-top: 2px;
        }

        h2 {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #334155;
            margin: 18px 0 6px 0;
            padding-bottom: 4px;
            border-bottom: 1px solid #e2e8f0;
        }

        .data {
            width: 100%;
            border-collapse: collapse;
        }

        .data th {
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            text-align: left;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 5px 6px;
        }

        .data td {
            font-size: 9px;
            border-bottom: 1px solid #f1f5f9;
            padding: 5px 6px;
            vertical-align: middle;
        }

        .data .right {
            text-align: right;
        }

        .data .muted {
            color: #64748b;
        }

        .data .strong {
            font-weight: bold;
        }

        .color-bar {
            width: 5px;
            height: 18px;
        }

        .empty {
            padding: 12px;
            text-align: center;
            color: #94a3b8;
            border: 1px dashed #e2e8f0;
            border-radius: 6px;
            font-size: 9px;
        }

        .footer {
            margin-top: 22px;
            padding-top: 8px;
            border-top: 1px solid #e2e8f0;
            font-size: 8px;
            color: #94a3b8;
            text-align: center;
        }
    </style>
</head>
<body>
    <table class="header">
        <tr>
            <td>
                <div class="brand">
                    DOMPETKU
                    <small>Personal expense tracker</small>
                </div>
            </td>
            <td>
                <div class="doc-title">Laporan Keuangan</div>
                <div class="doc-subtitle">{{ nama_bulan($month) }}</div>
            </td>
        </tr>
    </table>

    <div class="divider"></div>

    <table class="meta">
        <tr>
            <td>Nama pengguna</td>
            <td class="value">{{ $user->name }}</td>
            <td>Periode</td>
            <td class="value">{{ $month->copy()->startOfMonth()->format('d/m/Y') }} — {{ $month->copy()->endOfMonth()->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td>Email</td>
            <td class="value">{{ $user->email }}</td>
            <td>Dibuat pada</td>
            <td class="value">{{ now()->format('d/m/Y H:i') }}</td>
        </tr>
    </table>

    <table class="summary">
        <tr>
            <td>
                <div class="label">Pemasukan</div>
                <div class="amount income">{{ rupiah($income) }}</div>
            </td>
            <td>
                <div class="label">Pengeluaran</div>
                <div class="amount expense">{{ rupiah($expense) }}</div>
                @if ($expenseDelta !== null)
                    <div class="note">{{ $expenseDelta > 0 ? '+' : '' }}{{ $expenseDelta }}% dari bulan lalu</div>
                @endif
            </td>
            <td>
                <div class="label">Selisih</div>
                <div class="amount {{ $income - $expense >= 0 ? 'income' : 'expense' }}">
                    {{ $income - $expense >= 0 ? '+' : '' }}{{ rupiah($income - $expense) }}
                </div>
            </td>
            <td>
                <div class="label">Jumlah transaksi</div>
                <div class="amount neutral">{{ $transactions->count() }}</div>
                <div class="note">Seluruh transaksi bulan ini</div>
            </td>
        </tr>
    </table>

    @php($expenseTotal = $expenseByCategory->sum('total'))
    @php($incomeTotal = $incomeByCategory->sum('total'))

    <h2>Pengeluaran per kategori</h2>

    @if ($expenseByCategory->isEmpty())
        <div class="empty">Belum ada pengeluaran pada periode ini.</div>
    @else
        <table class="data">
            <thead>
                <tr>
                    <th style="width: 12px;"></th>
                    <th>Kategori</th>
                    <th class="right" style="width: 110px;">Total</th>
                    <th class="right" style="width: 50px;">Porsi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($expenseByCategory as $item)
                    <tr>
                        <td><div class="color-bar" style="background-color: {{ $item['color'] }};"></div></td>
                        <td>{{ $item['name'] }}</td>
                        <td class="right strong">{{ rupiah($item['total']) }}</td>
                        <td class="right muted">{{ $expenseTotal > 0 ? round($item['total'] / $expenseTotal * 100, 1) : 0 }}%</td>
                    </tr>
                @endforeach
                <tr>
                    <td></td>
                    <td class="strong">Total pengeluaran</td>
                    <td class="right strong expense">{{ rupiah($expenseTotal) }}</td>
                    <td class="right muted">100%</td>
                </tr>
            </tbody>
        </table>
    @endif

    <h2>Pemasukan per kategori</h2>

    @if ($incomeByCategory->isEmpty())
        <div class="empty">Belum ada pemasukan pada periode ini.</div>
    @else
        <table class="data">
            <thead>
                <tr>
                    <th style="width: 12px;"></th>
                    <th>Kategori</th>
                    <th class="right" style="width: 110px;">Total</th>
                    <th class="right" style="width: 50px;">Porsi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($incomeByCategory as $item)
                    <tr>
                        <td><div class="color-bar" style="background-color: {{ $item['color'] }};"></div></td>
                        <td>{{ $item['name'] }}</td>
                        <td class="right strong">{{ rupiah($item['total']) }}</td>
                        <td class="right muted">{{ $incomeTotal > 0 ? round($item['total'] / $incomeTotal * 100, 1) : 0 }}%</td>
                    </tr>
                @endforeach
                <tr>
                    <td></td>
                    <td class="strong">Total pemasukan</td>
                    <td class="right strong income">{{ rupiah($incomeTotal) }}</td>
                    <td class="right muted">100%</td>
                </tr>
            </tbody>
        </table>
    @endif

    <h2>Aktivitas per dompet</h2>

    @if ($byWallet->isEmpty())
        <div class="empty">Belum ada aktivitas dompet pada periode ini.</div>
    @else
        <table class="data">
            <thead>
                <tr>
                    <th style="width: 12px;"></th>
                    <th>Dompet</th>
                    <th class="right" style="width: 110px;">Masuk</th>
                    <th class="right" style="width: 110px;">Keluar</th>
                    <th class="right" style="width: 110px;">Selisih</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($byWallet as $item)
                    <tr>
                        <td><div class="color-bar" style="background-color: {{ $item['color'] }};"></div></td>
                        <td>{{ $item['name'] }}</td>
                        <td class="right income">{{ rupiah($item['income']) }}</td>
                        <td class="right expense">{{ rupiah($item['expense']) }}</td>
                        <td class="right strong">{{ rupiah($item['income'] - $item['expense']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <h2>Rincian transaksi</h2>

    @if ($transactions->isEmpty())
        <div class="empty">Belum ada transaksi pada periode ini.</div>
    @else
        <table class="data">
            <thead>
                <tr>
                    <th style="width: 58px;">Tanggal</th>
                    <th>Kategori</th>
                    <th style="width: 90px;">Dompet</th>
                    <th>Catatan</th>
                    <th class="right" style="width: 90px;">Nominal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($transactions as $transaction)
                    <tr>
                        <td class="muted">{{ $transaction->date->format('d/m/Y') }}</td>
                        <td>{{ $transaction->category->name ?? 'Tanpa kategori' }}</td>
                        <td class="muted">{{ $transaction->wallet->name ?? '-' }}</td>
                        <td class="muted">{{ $transaction->note ?: '—' }}</td>
                        <td class="right strong {{ $transaction->type === 'income' ? 'income' : 'expense' }}">
                            {{ $transaction->type === 'income' ? '+' : '-' }}{{ rupiah($transaction->amount) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        Laporan ini dibuat otomatis oleh {{ config('app.name', 'Dompetku') }} pada {{ now()->format('d/m/Y H:i') }}.
    </div>
</body>
</html>
