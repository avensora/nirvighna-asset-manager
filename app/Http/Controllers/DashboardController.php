<?php

namespace App\Http\Controllers;

use App\Enums\InvoiceStatus;
use App\Enums\TransactionType;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Project;
use App\Models\ScheduledExpense;
use App\Models\Transaction;
use App\Models\User;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user          = auth()->user();
        $isMasterAdmin = $user->isMasterAdmin();
        $isManager     = $user->isManager();

        // Team Lead dashboard
        if (! $isManager) {
            $assignedProjects = Project::whereHas('assignments', fn ($q) => $q->where('user_id', $user->id))
                ->with('client')
                ->orderByRaw('FIELD(status, "active", "planning", "on_hold", "completed", "cancelled")')
                ->orderBy('deadline')
                ->get();

            $recentActivity = \Spatie\Activitylog\Models\Activity::with('causer')
                ->where('causer_id', $user->id)
                ->where('causer_type', User::class)
                ->latest()
                ->limit(10)
                ->get();

            return view('dashboard.index', [
                'isMasterAdmin'    => false,
                'isManager'        => false,
                'assignedProjects' => $assignedProjects,
                'recentActivity'   => $recentActivity,
            ]);
        }

        // Manager & MasterAdmin dashboard
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

        $start = $pivot->copy()->startOfMonth();
        $end   = $pivot->copy()->endOfMonth();

        $monthTotals = Transaction::selectRaw('type, SUM(amount) as total')
            ->whereBetween('date', [$start, $end])
            ->groupBy('type')
            ->get()
            ->keyBy('type');

        $revenueThisMonth  = (float) ($monthTotals->get('income')?->total  ?? 0);
        $expensesThisMonth = (float) ($monthTotals->get('expense')?->total ?? 0);
        $netProfit         = $revenueThisMonth - $expensesThisMonth;

        $outstandingInvoices = Invoice::whereIn('status', [InvoiceStatus::Sent, InvoiceStatus::Partial])
            ->withSum('payments', 'amount')
            ->get();

        $outstandingTotal = (float) $outstandingInvoices->sum(fn ($inv) => max(0, (float)$inv->total - (float)($inv->payments_sum_amount ?? 0)));
        $outstandingCount = $outstandingInvoices->count();

        // Notify manager once per overdue invoice
        Invoice::whereIn('status', [InvoiceStatus::Sent, InvoiceStatus::Partial])
            ->whereNotNull('due_date')
            ->where('due_date', '<', Carbon::today())
            ->select('id', 'invoice_number', 'due_date')
            ->get()
            ->each(function ($invoice) {
                NotificationService::notifyOnce(
                    auth()->id(),
                    'invoice_overdue',
                    "Overdue invoice: {$invoice->invoice_number}",
                    "Invoice {$invoice->invoice_number} was due on {$invoice->due_date->format('d M Y')} and is still unpaid.",
                    ['invoice_id' => $invoice->id]
                );
            });

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

        // Project pipeline by status
        $projectPipelineRaw = Project::selectRaw('status, COUNT(*) as cnt')
            ->groupBy('status')
            ->pluck('cnt', 'status');

        // Lead pipeline by stage
        $leadPipelineRaw = Lead::selectRaw('stage, COUNT(*) as cnt')
            ->groupBy('stage')
            ->pluck('cnt', 'stage');

        // MasterAdmin-only extras
        $userCount      = 0;
        $leadsTotal     = 0;
        $leadsWon       = 0;
        $conversionRate = 0;

        if ($isMasterAdmin) {
            $userCount  = User::count();
            $leadsTotal = Lead::count();
            $leadsWon   = Lead::where('stage', 'won')->count();
            $leadsClosed = Lead::whereIn('stage', ['won', 'lost'])->count();
            $conversionRate = $leadsClosed > 0 ? round($leadsWon / $leadsClosed * 100, 1) : 0;
        }

        $recentActivity = \Spatie\Activitylog\Models\Activity::with('causer')
            ->latest()
            ->limit(8)
            ->get();

        return view('dashboard.index', compact(
            'isMasterAdmin',
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
            'upcomingExpenses',
            'projectPipelineRaw',
            'leadPipelineRaw',
            'userCount',
            'leadsTotal',
            'leadsWon',
            'conversionRate'
        ));
    }
}
