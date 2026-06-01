<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use App\Models\MonthClosing;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MonthClosingController extends Controller
{
    public function index(): View
    {
        $closings = MonthClosing::with('closer')->orderByDesc('year')->orderByDesc('month')->get();
        return view('month-closings.index', compact('closings'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'year'  => ['required', 'integer', 'min:2000', 'max:2100'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
        ]);

        $year  = (int) $data['year'];
        $month = (int) $data['month'];

        // Cannot close current or future month
        $now = now();
        if ($year > $now->year || ($year === $now->year && $month >= $now->month)) {
            return back()->with('error', 'You can only close past months.');
        }

        if (MonthClosing::isClosed($year, $month)) {
            return back()->with('error', "This month is already closed.");
        }

        $totalIncome   = (float) Transaction::where('approval_status', 'approved')
            ->where('type', 'income')
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->sum('amount');

        $totalExpenses = (float) Transaction::where('approval_status', 'approved')
            ->where('type', 'expense')
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->sum('amount');

        $openingBalance = (float) CompanySetting::get('opening_balance', 0);
        $closingBalance = CompanySetting::currentBalance();

        MonthClosing::create([
            'year'            => $year,
            'month'           => $month,
            'closed_by'       => auth()->id(),
            'closed_at'       => now(),
            'opening_balance' => $openingBalance,
            'closing_balance' => $closingBalance,
            'total_income'    => $totalIncome,
            'total_expenses'  => $totalExpenses,
        ]);

        $monthLabel = \Carbon\Carbon::createFromDate($year, $month, 1)->format('F Y');

        activity()
            ->causedBy(auth()->user())
            ->log("Closed month: {$monthLabel}");

        return back()->with('success', "{$monthLabel} closed successfully.");
    }
}
