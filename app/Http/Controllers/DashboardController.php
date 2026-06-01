<?php

namespace App\Http\Controllers;

use App\Enums\InvoiceStatus;
use App\Enums\TransactionType;
use App\Models\Invoice;
use App\Models\ScheduledExpense;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $isManager  = auth()->user()->isManager();

        $recentActivity = \Spatie\Activitylog\Models\Activity::with('causer')
            ->latest()
            ->limit(8)
            ->get();

        $monthParam = $request->query('month');
        try {
            $pivot = $monthParam
                ? Carbon::createFromFormat('Y-m', $monthParam)->startOfMonth()
                : Carbon::now()->startOfMonth();
        } catch (\Exception) {
            $pivot = Carbon::now()->startOfMonth();
        }

        $selectedMonth  = $pivot->format('Y-m');
        $monthLabel     = $pivot->format('F Y');
        $isCurrentMonth = $pivot->isSameMonth(Carbon::now());

        if (!$isManager) {
            return view('dashboard.index', [
                'isManager'      => false,
                'recentActivity' => $recentActivity,
                'monthLabel'     => $monthLabel,
                'selectedMonth'  => $selectedMonth,
                'isCurrentMonth' => $isCurrentMonth,
            ]);
        }

        $start = $pivot->copy()->startOfMonth();
        $end   = $pivot->copy()->endOfMonth();

        // Single query for current-month revenue + expenses
        $monthTotals = Transaction::selectRaw('type, SUM(amount) as total')
            ->whereBetween('date', [$start, $end])
            ->groupBy('type')
            ->get()
            ->keyBy('type');

        $revenueThisMonth  = (float) ($monthTotals->get('income')?->total  ?? 0);
        $expensesThisMonth = (float) ($monthTotals->get('expense')?->total ?? 0);
        $netProfit         = $revenueThisMonth - $expensesThisMonth;

        // Single query for outstanding invoice count + total
        $outstanding = Invoice::whereIn('status', [InvoiceStatus::Draft, InvoiceStatus::Sent])
            ->selectRaw('COUNT(*) as cnt, SUM(total) as ttl')
            ->first();

        $outstandingTotal = (float) ($outstanding->ttl ?? 0);
        $outstandingCount = (int)   ($outstanding->cnt ?? 0);

        // Single query for the 6-month chart (replaces 12 individual queries)
        $sixMonthStart = $pivot->copy()->subMonths(5)->startOfMonth();
        $sixMonthEnd   = $pivot->copy()->endOfMonth();

        $rawStats = Transaction::selectRaw('YEAR(date) as yr, MONTH(date) as mo, type, SUM(amount) as total')
            ->whereBetween('date', [$sixMonthStart, $sixMonthEnd])
            ->groupByRaw('YEAR(date), MONTH(date), type')
            ->get()
            ->groupBy(fn ($r) => $r->yr . '-' . (int) $r->mo);

        $chartMonths   = [];
        $chartIncome   = [];
        $chartExpenses = [];

        for ($i = 5; $i >= 0; $i--) {
            $month  = $pivot->copy()->subMonths($i);
            $key    = $month->year . '-' . $month->month;
            $slice  = $rawStats->get($key, collect());

            $chartMonths[]   = $month->format('M Y');
            $chartIncome[]   = (float) ($slice->firstWhere('type', 'income')?->total  ?? 0);
            $chartExpenses[] = (float) ($slice->firstWhere('type', 'expense')?->total ?? 0);
        }

        $upcomingExpenses = ScheduledExpense::where('status', 'pending')
            ->where('due_date', '<=', Carbon::now()->addDays(30))
            ->orderBy('due_date')
            ->get();

        return view('dashboard.index', compact(
            'isManager',
            'revenueThisMonth',
            'expensesThisMonth',
            'netProfit',
            'outstandingTotal',
            'outstandingCount',
            'chartMonths',
            'chartIncome',
            'chartExpenses',
            'recentActivity',
            'monthLabel',
            'selectedMonth',
            'isCurrentMonth',
            'upcomingExpenses'
        ));
    }
}
