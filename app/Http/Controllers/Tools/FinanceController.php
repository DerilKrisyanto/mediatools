<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Str;

class FinanceController extends Controller
{
 
    /* ════════════════════════════════════════════════════
       DASHBOARD – GET /finance
       ════════════════════════════════════════════════════ */
    public function index(Request $request)
    {
        $month = (int) $request->get('month', now()->month);
        $year  = (int) $request->get('year',  now()->year);
        $type  = $request->get('type', 'all'); // Filter jenis transaksi

        // 1. Ambil Statistik dalam SATU Query (Sangat Cepat)
        $stats = Transaction::mine()
            ->inMonth($month, $year)
            ->selectRaw("
                SUM(CASE WHEN type = 'income' THEN total_amount ELSE 0 END) as total_income,
                SUM(CASE WHEN type = 'expense' THEN total_amount ELSE 0 END) as total_expense,
                COUNT(*) as total_count,
                COUNT(CASE WHEN type = 'income' THEN 1 END) as income_count,
                COUNT(CASE WHEN type = 'expense' THEN 1 END) as expense_count
            ")
            ->first();

        $totalIncome  = (float) $stats->total_income;
        $totalExpense = (float) $stats->total_expense;
        $balance      = $totalIncome - $totalExpense;
        $txCount      = (int) $stats->total_count;
        $incTxCount   = (int) $stats->income_count;
        $expTxCount   = (int) $stats->expense_count;

        // 2. Query Transaksi dengan Pagination & Filter
        $query = Transaction::mine()->inMonth($month, $year);
        if ($type !== 'all') {
            $query->where('type', $type);
        }

        $transactions = $query->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString(); // Agar filter bulan/tahun tetap terbawa saat pindah halaman

        // 3. Optimasi Grafik (Ambil data 6 bulan dalam SATU query)
        $startDate = now()->subMonths(5)->startOfMonth();
        $chartRaw = Transaction::mine()
            ->where('transaction_date', '>=', $startDate)
            ->selectRaw("
                DATE_FORMAT(transaction_date, '%Y-%m') as month_year,
                SUM(CASE WHEN type = 'income' THEN total_amount ELSE 0 END) as income,
                SUM(CASE WHEN type = 'expense' THEN total_amount ELSE 0 END) as expense
            ")
            ->groupBy('month_year')
            ->get()
            ->keyBy('month_year');

        $chartLabels = []; $chartIncome = []; $chartExpense = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $key = $date->format('Y-m');
            $chartLabels[]  = $date->locale('id')->isoFormat('MMM YY');
            $chartIncome[]  = (float) ($chartRaw[$key]->income ?? 0);
            $chartExpense[] = (float) ($chartRaw[$key]->expense ?? 0);
        }

        // Daftar tahun untuk filter
        $years = Transaction::mine()
            ->selectRaw('YEAR(transaction_date) as yr')
            ->distinct()->orderBy('yr', 'desc')->pluck('yr');
        if ($years->isEmpty()) $years = collect([now()->year]);

        return view('tools.finance.index', compact(
            'transactions', 'totalIncome', 'totalExpense', 'balance', 
            'txCount', 'incTxCount', 'expTxCount', 'month', 'year', 'years',
            'chartLabels', 'chartIncome', 'chartExpense', 'type'
        ));
    }
 
    /* ════════════════════════════════════════════════════
       SIMPAN TRANSAKSI – POST /finance/transactions
       ════════════════════════════════════════════════════ */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type'             => 'required|in:income,expense',
            'name'             => 'required|string|max:255',
            'quantity'         => 'required|numeric|min:0.01|max:999999',
            'price_per_item'   => 'required|numeric|min:0|max:99999999999',
            'transaction_date' => 'required|date',
            'notes'            => 'nullable|string|max:500',
        ], [
            'type.required'             => 'Jenis transaksi wajib dipilih.',
            'name.required'             => 'Nama transaksi wajib diisi.',
            'quantity.required'         => 'Jumlah item wajib diisi.',
            'quantity.min'              => 'Jumlah item minimal 0.01.',
            'price_per_item.required'   => 'Harga per item wajib diisi.',
            'price_per_item.min'        => 'Harga tidak boleh negatif.',
            'transaction_date.required' => 'Tanggal transaksi wajib diisi.',
        ]);
 
        $validated['user_id']      = auth()->id();
        $validated['total_amount'] = $validated['quantity'] * $validated['price_per_item'];
 
        Transaction::create($validated);
 
        return redirect()
            ->route('tools.finance', [
                'month' => Carbon::parse($validated['transaction_date'])->month,
                'year'  => Carbon::parse($validated['transaction_date'])->year,
            ])
            ->with('success', 'Transaksi berhasil disimpan! 🎉');
    }
 
    /* ════════════════════════════════════════════════════
       HAPUS TRANSAKSI – DELETE /finance/transactions/{id}
       ════════════════════════════════════════════════════ */
    public function destroy(int $id, Request $request)
    {
        $transaction = Transaction::mine()->findOrFail($id);
        $transaction->delete();
 
        return redirect()
            ->route('tools.finance', [
                'month' => $request->get('month', now()->month),
                'year'  => $request->get('year',  now()->year),
            ])
            ->with('success', 'Transaksi berhasil dihapus.');
    }
 
    /* ════════════════════════════════════════════════════
       CETAK / EXPORT PDF – GET /finance/print
       ════════════════════════════════════════════════════ */
    public function print(Request $request)
    {
        $type      = $request->get('type', 'all');   // all | income | expense
        $dateFrom  = $request->get('date_from');
        $dateTo    = $request->get('date_to');
        $month     = $request->get('month');
        $year      = $request->get('year');
        $filterBy  = $request->get('filter_by', 'month'); // month | year | range
 
        $query = Transaction::mine()
            ->orderBy('transaction_date', 'asc');
 
        // Filter berdasarkan jenis transaksi
        if ($type === 'income')  $query->income();
        if ($type === 'expense') $query->expense();
 
        // Filter berdasarkan rentang tanggal
        switch ($filterBy) {
            case 'range':
                if ($dateFrom && $dateTo) {
                    $query->betweenDates($dateFrom, $dateTo);
                }
                break;
            case 'year':
                if ($year) $query->inYear((int) $year);
                break;
            case 'month':
            default:
                $m = $month ?: now()->month;
                $y = $year  ?: now()->year;
                $query->inMonth((int) $m, (int) $y);
                break;
        }
 
        $transactions = $query->get();
 
        $totalIncome  = $transactions->where('type', 'income')->sum('total_amount');
        $totalExpense = $transactions->where('type', 'expense')->sum('total_amount');
        $balance      = $totalIncome - $totalExpense;
 
        $periodLabel = match ($filterBy) {
            'range' => ($dateFrom ? Carbon::parse($dateFrom)->isoFormat('D MMM YYYY') : '') .
                       ' – ' .
                       ($dateTo  ? Carbon::parse($dateTo)->isoFormat('D MMM YYYY')   : ''),
            'year'  => 'Tahun ' . ($year ?: now()->year),
            default => Carbon::createFromDate($year ?: now()->year, $month ?: now()->month, 1)
                            ->locale('id')->isoFormat('MMMM YYYY'),
        };
 
        $typeLabel = match ($type) {
            'income'  => 'Laporan Pemasukan',
            'expense' => 'Laporan Pengeluaran',
            default   => 'Laporan Keuangan Lengkap',
        };
 
        return view('tools.finance.pdf', compact(
            'transactions', 'totalIncome', 'totalExpense',
            'balance', 'periodLabel', 'typeLabel', 'type'
        ));
    }
}
