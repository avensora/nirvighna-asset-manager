<?php

namespace App\Http\Controllers;

use App\Enums\TransactionType;
use App\Models\CompanySetting;
use App\Models\MonthClosing;
use App\Models\Project;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        $query = Transaction::with('user')->orderBy('date', 'desc')->orderBy('id', 'desc');

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('month')) {
            [$year, $month] = explode('-', $request->input('month'));
            $query->whereYear('date', $year)->whereMonth('date', $month);
        }

        if ($request->filled('approval')) {
            $query->where('approval_status', $request->input('approval'));
        }

        $totals = (clone $query)->reorder()->where('approval_status', 'approved')->selectRaw(
            'SUM(CASE WHEN type = "income"  THEN amount ELSE 0 END) as income,
             SUM(CASE WHEN type = "expense" THEN amount ELSE 0 END) as expense'
        )->first();

        $pendingCount = Transaction::where('approval_status', 'pending')->count();

        $transactions = $query->paginate(25)->withQueryString();

        $currentBalance  = CompanySetting::currentBalance();
        $openingBalance  = (float) CompanySetting::get('opening_balance', 0);
        $openingBalanceDate = CompanySetting::get('opening_balance_date');

        return view('transactions.index', compact('transactions', 'totals', 'currentBalance', 'openingBalance', 'openingBalanceDate', 'pendingCount'));
    }

    public function export(Request $request): mixed
    {
        $query = Transaction::orderBy('date', 'desc')->orderBy('id', 'desc');

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('month')) {
            [$year, $month] = explode('-', $request->input('month'));
            $query->whereYear('date', $year)->whereMonth('date', $month);
        }

        $transactions = $query->get();

        $totals = [
            'income'  => $transactions->filter(fn ($t) => $t->type === TransactionType::Income)->sum('amount'),
            'expense' => $transactions->filter(fn ($t) => $t->type === TransactionType::Expense)->sum('amount'),
        ];

        if ($request->input('format') === 'pdf') {
            $pdf = Pdf::loadView('transactions.export-pdf', compact('transactions', 'totals'))
                ->setPaper('a4', 'landscape');
            return $pdf->download('transactions-' . now()->format('Y-m-d') . '.pdf');
        }

        // Default: CSV
        $filename = 'transactions-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($transactions) {
            // Guard against CSV formula injection (Excel/Sheets treat leading =,+,@,- as formulas)
            $safe = static fn (string $v): string =>
                preg_match('/^[=+\-@\t\r]/', $v) ? "'" . $v : $v;

            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date', 'Type', 'Category', 'Description', 'Reference', 'Amount (INR)']);
            foreach ($transactions as $t) {
                fputcsv($handle, [
                    $t->date->format('d-m-Y'),
                    $t->type->label(),
                    $safe($t->category),
                    $safe($t->description ?? ''),
                    $safe($t->reference ?? ''),
                    number_format((float) $t->amount, 2, '.', ''),
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function create(): View
    {
        $projects = Project::orderBy('title')->get();
        return view('transactions.create', compact('projects'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'type'        => 'required|in:income,expense',
            'category'    => 'required|string|max:100',
            'amount'      => 'required|numeric|min:0.01',
            'date'        => 'required|date',
            'description' => 'nullable|string',
            'reference'   => 'nullable|string|max:100',
            'project_id'  => 'nullable|exists:projects,id',
        ]);

        $data['user_id']         = auth()->id();
        $data['approval_status'] = auth()->user()->isManager() ? 'approved' : 'pending';

        $transaction = Transaction::create($data);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($transaction)
            ->withProperties(['type' => $transaction->type->label(), 'amount' => $transaction->amount])
            ->log("Recorded {$transaction->type->label()}: {$transaction->category}");

        $message = auth()->user()->isManager()
            ? ucfirst($transaction->type->label()) . ' of ' . format_inr((float) $transaction->amount) . ' recorded.'
            : 'Expense request submitted and pending manager approval.';

        return redirect()->route('transactions.index')->with('success', $message);
    }

    public function edit(Transaction $transaction): View
    {
        $projects = Project::orderBy('title')->get();
        return view('transactions.edit', compact('transaction', 'projects'));
    }

    public function update(Request $request, Transaction $transaction): RedirectResponse
    {
        if (MonthClosing::isClosed($transaction->date->year, $transaction->date->month)) {
            return back()->with('error', "Cannot edit a transaction in a closed period ({$transaction->date->format('M Y')}).");
        }

        $data = $request->validate([
            'type'        => 'required|in:income,expense',
            'category'    => 'required|string|max:100',
            'amount'      => 'required|numeric|min:0.01',
            'date'        => 'required|date',
            'description' => 'nullable|string',
            'reference'   => 'nullable|string|max:100',
            'project_id'  => 'nullable|exists:projects,id',
        ]);

        $transaction->update($data);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($transaction)
            ->withProperties(['type' => $transaction->type->label(), 'amount' => $transaction->amount])
            ->log("Updated {$transaction->type->label()}: {$transaction->category}");

        return redirect()->route('transactions.index')
            ->with('success', 'Transaction updated.');
    }

    public function destroy(Request $request, Transaction $transaction): RedirectResponse
    {
        if (MonthClosing::isClosed($transaction->date->year, $transaction->date->month)) {
            return back()->with('error', "Cannot void a transaction in a closed period ({$transaction->date->format('M Y')}).");
        }

        $label = "{$transaction->type->label()}: {$transaction->category}";

        if ($request->filled('void_reason')) {
            $transaction->void_reason = $request->input('void_reason');
            $transaction->save();
        }

        activity()
            ->causedBy(auth()->user())
            ->withProperties(['type' => $transaction->type->label(), 'amount' => $transaction->amount])
            ->log("Voided transaction: {$label}");

        $transaction->delete();

        return redirect()->route('transactions.index')
            ->with('success', 'Transaction voided.');
    }

    public function approve(Transaction $transaction): RedirectResponse
    {
        if ($transaction->approval_status !== 'pending') {
            return back()->with('error', 'Transaction is not pending approval.');
        }

        $transaction->update([
            'approval_status' => 'approved',
            'approved_by'     => auth()->id(),
            'approved_at'     => now(),
        ]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($transaction)
            ->log("Approved transaction: {$transaction->category}");

        return back()->with('success', 'Transaction approved.');
    }

    public function reject(Request $request, Transaction $transaction): RedirectResponse
    {
        $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        if ($transaction->approval_status !== 'pending') {
            return back()->with('error', 'Transaction is not pending approval.');
        }

        $transaction->update([
            'approval_status' => 'rejected',
            'rejection_reason' => $request->input('rejection_reason'),
        ]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($transaction)
            ->log("Rejected transaction: {$transaction->category}");

        return back()->with('success', 'Transaction rejected.');
    }
}
