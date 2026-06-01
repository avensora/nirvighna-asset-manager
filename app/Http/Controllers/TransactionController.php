<?php

namespace App\Http\Controllers;

use App\Enums\TransactionType;
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
            $query->where('type', $request->type);
        }

        if ($request->filled('month')) {
            [$year, $month] = explode('-', $request->month);
            $query->whereYear('date', $year)->whereMonth('date', $month);
        }

        $totals = (clone $query)->reorder()->selectRaw(
            'SUM(CASE WHEN type = "income"  THEN amount ELSE 0 END) as income,
             SUM(CASE WHEN type = "expense" THEN amount ELSE 0 END) as expense'
        )->first();

        $transactions = $query->paginate(25)->withQueryString();

        return view('transactions.index', compact('transactions', 'totals'));
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
        return view('transactions.create');
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
        ]);

        $data['user_id'] = auth()->id();

        $transaction = Transaction::create($data);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($transaction)
            ->withProperties(['type' => $transaction->type->label(), 'amount' => $transaction->amount])
            ->log("Recorded {$transaction->type->label()}: {$transaction->category}");

        return redirect()->route('transactions.index')
            ->with('success', ucfirst($transaction->type->label()) . ' of ' . format_inr((float) $transaction->amount) . ' recorded.');
    }

    public function edit(Transaction $transaction): View
    {
        return view('transactions.edit', compact('transaction'));
    }

    public function update(Request $request, Transaction $transaction): RedirectResponse
    {
        $data = $request->validate([
            'type'        => 'required|in:income,expense',
            'category'    => 'required|string|max:100',
            'amount'      => 'required|numeric|min:0.01',
            'date'        => 'required|date',
            'description' => 'nullable|string',
            'reference'   => 'nullable|string|max:100',
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

    public function destroy(Transaction $transaction): RedirectResponse
    {
        $label = "{$transaction->type->label()}: {$transaction->category}";

        activity()
            ->causedBy(auth()->user())
            ->withProperties(['type' => $transaction->type->label(), 'amount' => $transaction->amount])
            ->log("Deleted transaction: {$label}");

        $transaction->delete();

        return redirect()->route('transactions.index')
            ->with('success', 'Transaction deleted.');
    }
}
