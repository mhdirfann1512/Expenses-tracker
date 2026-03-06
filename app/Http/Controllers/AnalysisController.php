<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class AnalysisController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();
        
        // 1. Ambil input dari filter, kalau takde, guna bulan/tahun sekarang
        $selectedMonth = $request->input('month', now()->month);
        $selectedYear = $request->input('year', now()->year);

        // Kita buat base query untuk user ni supaya senang nak filter
        $baseQuery = Expense::where('user_id', $userId);

        // 2. Data Belanja Bulan Dipilih
        $thisMonthTotal = (clone $baseQuery)
            ->whereYear('spent_at', $selectedYear)
            ->whereMonth('spent_at', $selectedMonth)
            ->sum('amount');

        // 3. Data Belanja Bulan Lepas (Relative kepada bulan yang dipilih)
        $lastMonth = now()->create($selectedYear, $selectedMonth, 1)->subMonth();
        $lastMonthTotal = (clone $baseQuery)
            ->whereYear('spent_at', $lastMonth->year)
            ->whereMonth('spent_at', $lastMonth->month)
            ->sum('amount');

        // 4. Kira Peratus Perbezaan
        $difference = $thisMonthTotal - $lastMonthTotal;
        $percentageChange = ($lastMonthTotal > 0) ? ($difference / $lastMonthTotal) * 100 : 0;

        // 5. Data Carta Pai (Filtered)
        $categoryData = Expense::where('expenses.user_id', $userId)
            ->join('categories', 'expenses.category_id', '=', 'categories.id')
            ->whereYear('expenses.spent_at', $selectedYear)
            ->whereMonth('expenses.spent_at', $selectedMonth)
            ->select('categories.name', DB::raw('SUM(expenses.amount) as total'), 'categories.color')
            ->groupBy('categories.name', 'categories.color')
            ->get();

        // 6. Data Carta Bar (Maintain 6 bulan terakhir untuk trend)
        $monthlyData = (clone $baseQuery)
            ->select(
                DB::raw("DATE_FORMAT(spent_at, '%Y-%m') as sort_key"),
                DB::raw("DATE_FORMAT(spent_at, '%b %Y') as month"),
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('sort_key', 'month')
            ->orderBy('sort_key', 'desc') // Kita nak yang terbaru kat atas
            ->take(6)
            ->get()
            ->sortBy('sort_key'); // Susun balik supaya graf bar cantik dari kiri ke kanan

        return view('analysis', compact(
            'categoryData', 
            'monthlyData', 
            'thisMonthTotal', 
            'lastMonthTotal', 
            'percentageChange',
            'difference'
        ));
    }
}