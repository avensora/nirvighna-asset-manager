<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Loan;
use App\Models\Reimbursement;
use App\Models\Transaction;
use Illuminate\View\View;

class OwesController extends Controller
{
    public function index(): View
    {
        // 1. Company owes employees — pending + approved reimbursements grouped by user
        $pendingReimbursements = Reimbursement::whereIn('status', ['pending', 'approved'])
            ->with('user')
            ->get()
            ->groupBy('user_id')
            ->map(fn ($items) => [
                'user'  => $items->first()->user,
                'total' => (float) $items->sum('amount'),
                'count' => $items->count(),
                'items' => $items,
            ])
            ->sortByDesc('total')
            ->values();

        // 2. Clients owe company — sent + partial invoices grouped by client
        $outstandingInvoices = Invoice::whereIn('status', ['sent', 'partial'])
            ->with(['client', 'payments'])
            ->get()
            ->groupBy('client_id')
            ->map(fn ($items) => [
                'client'        => $items->first()->client,
                'total_due'     => $items->sum(fn ($inv) => $inv->amountDue()),
                'invoice_count' => $items->count(),
                'items'         => $items,
            ])
            ->sortByDesc('total_due')
            ->values();

        // 3. Company owes lenders — outstanding + partially repaid loans
        $outstandingLoans = Loan::whereIn('status', ['outstanding', 'partially_repaid'])
            ->withSum('repayments', 'amount')
            ->latest('borrowed_date')
            ->get()
            ->map(fn ($loan) => [
                'loan'        => $loan,
                'outstanding' => $loan->amountOutstanding(),
            ])
            ->sortByDesc('outstanding')
            ->values();

        // 4. Company net balance from transactions
        $totalIncome  = (float) Transaction::where('type', 'income')->sum('amount');
        $totalExpense = (float) Transaction::where('type', 'expense')->sum('amount');
        $netBalance   = $totalIncome - $totalExpense;

        // Summaries
        $totalOwedToEmployees = $pendingReimbursements->sum('total');
        $totalOwedByClients   = $outstandingInvoices->sum('total_due');
        $totalOwedToLenders   = $outstandingLoans->sum('outstanding');

        return view('owes.index', compact(
            'pendingReimbursements',
            'outstandingInvoices',
            'outstandingLoans',
            'netBalance',
            'totalIncome',
            'totalExpense',
            'totalOwedToEmployees',
            'totalOwedByClients',
            'totalOwedToLenders'
        ));
    }
}
