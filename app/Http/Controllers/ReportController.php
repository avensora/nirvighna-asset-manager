<?php

namespace App\Http\Controllers;

use App\Enums\InvoiceStatus;
use App\Enums\LeadStage;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        return view('reports.index');
    }

    public function revenue(Request $request): View
    {
        $year = (int) $request->query('year', now()->year);

        $paidInvoices = Invoice::where('status', InvoiceStatus::Paid)
            ->whereYear('issue_date', $year)
            ->with('client')
            ->get();

        $byClient = $paidInvoices
            ->groupBy('client_id')
            ->map(function ($items) {
                $client = $items->first()->client;
                return [
                    'client_name' => $client?->name ?? 'Unknown',
                    'total'       => (float) $items->sum('total'),
                    'count'       => $items->count(),
                ];
            })
            ->sortByDesc('total')
            ->values();

        // Monthly breakdown for chart
        $monthly = collect(range(1, 12))->map(function ($month) use ($paidInvoices, $year) {
            return [
                'month' => Carbon::create($year, $month)->format('M'),
                'total' => (float) $paidInvoices->filter(fn ($i) => $i->issue_date->month === $month)->sum('total'),
            ];
        });

        $grandTotal   = (float) $paidInvoices->sum('total');
        $invoiceCount = $paidInvoices->count();

        $availableYears = Invoice::where('status', InvoiceStatus::Paid)
            ->selectRaw('YEAR(issue_date) as yr')
            ->distinct()
            ->orderByDesc('yr')
            ->pluck('yr')
            ->toArray();

        if (empty($availableYears)) {
            $availableYears = [now()->year];
        }

        return view('reports.revenue', compact(
            'byClient',
            'monthly',
            'grandTotal',
            'invoiceCount',
            'year',
            'availableYears'
        ));
    }

    public function leads(): View
    {
        $stageCounts = Lead::selectRaw('stage, COUNT(*) as cnt, COALESCE(SUM(deal_value), 0) as value')
            ->groupBy('stage')
            ->get()
            ->keyBy('stage');

        $total   = $stageCounts->sum('cnt');
        $won     = (int) ($stageCounts->get('won')?->cnt ?? 0);
        $lost    = (int) ($stageCounts->get('lost')?->cnt ?? 0);
        $closed  = $won + $lost;
        $wonRate = $closed > 0 ? round($won / $closed * 100, 1) : 0;

        $totalValue    = (float) $stageCounts->sum('value');
        $wonValue      = (float) ($stageCounts->get('won')?->value ?? 0);

        $stages = LeadStage::pipeline();

        return view('reports.leads', compact(
            'stageCounts',
            'total',
            'won',
            'lost',
            'closed',
            'wonRate',
            'totalValue',
            'wonValue',
            'stages'
        ));
    }

    public function projects(): View
    {
        $projects = Project::with('client', 'assignees')
            ->whereNotIn('status', ['cancelled'])
            ->orderByRaw('FIELD(status, "active", "planning", "on_hold", "completed")')
            ->orderBy('deadline')
            ->get();

        $totalProjects   = $projects->count();
        $activeCount     = $projects->where('status.value', 'active')->count();
        $overdueCount    = $projects->filter(fn ($p) => $p->isOverdue())->count();
        $avgProgress     = $totalProjects > 0 ? round($projects->avg('progress'), 1) : 0;

        return view('reports.projects', compact(
            'projects',
            'totalProjects',
            'activeCount',
            'overdueCount',
            'avgProgress'
        ));
    }

    public function invoices(): View
    {
        $today   = Carbon::today();
        $unpaid  = Invoice::whereIn('status', [InvoiceStatus::Sent, InvoiceStatus::Partial])
            ->with(['client', 'payments'])
            ->orderBy('due_date')
            ->get();

        $buckets = [
            'current'  => ['label' => 'Not Yet Due',   'color' => 'success',   'items' => collect()],
            '1_30'     => ['label' => '1–30 Days',      'color' => 'warning',   'items' => collect()],
            '31_60'    => ['label' => '31–60 Days',     'color' => 'orange',    'items' => collect()],
            '61_90'    => ['label' => '61–90 Days',     'color' => 'danger',    'items' => collect()],
            '90_plus'  => ['label' => '90+ Days',       'color' => 'dark',      'items' => collect()],
            'no_date'  => ['label' => 'No Due Date',    'color' => 'secondary', 'items' => collect()],
        ];

        foreach ($unpaid as $invoice) {
            if (! $invoice->due_date) {
                $buckets['no_date']['items']->push($invoice);
                continue;
            }
            if (! $invoice->due_date->isPast()) {
                $buckets['current']['items']->push($invoice);
            } else {
                $days = (int) $invoice->due_date->diffInDays($today);
                if ($days <= 30)      $buckets['1_30']['items']->push($invoice);
                elseif ($days <= 60)  $buckets['31_60']['items']->push($invoice);
                elseif ($days <= 90)  $buckets['61_90']['items']->push($invoice);
                else                  $buckets['90_plus']['items']->push($invoice);
            }
        }

        $totalUnpaid      = $unpaid->count();
        $totalUnpaidValue = (float) $unpaid->sum('total');

        return view('reports.invoices', compact(
            'buckets',
            'totalUnpaid',
            'totalUnpaidValue'
        ));
    }
}
