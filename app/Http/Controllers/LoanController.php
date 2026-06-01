<?php

namespace App\Http\Controllers;

use App\Enums\LoanSourceType;
use App\Models\Loan;
use App\Models\LoanRepayment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoanController extends Controller
{
    public function index(): View
    {
        $loans = Loan::withSum('repayments', 'amount')->latest('borrowed_date')->paginate(20);

        $totalBorrowed    = Loan::sum('principal_amount');
        $totalOutstanding = Loan::withSum('repayments', 'amount')->get()
            ->sum(fn ($loan) => max(0, (float) $loan->principal_amount - (float) ($loan->repayments_sum_amount ?? 0)));

        return view('loans.index', compact('loans', 'totalBorrowed', 'totalOutstanding'));
    }

    public function create(): View
    {
        $sourceTypes = LoanSourceType::cases();
        return view('loans.create', compact('sourceTypes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'source_name'      => ['required', 'string', 'max:255'],
            'source_type'      => ['required', 'in:person,bank,other'],
            'principal_amount' => ['required', 'numeric', 'min:0.01'],
            'borrowed_date'    => ['required', 'date'],
            'due_date'         => ['nullable', 'date', 'after_or_equal:borrowed_date'],
            'purpose'          => ['nullable', 'string', 'max:255'],
            'notes'            => ['nullable', 'string', 'max:2000'],
        ]);

        $data['created_by'] = auth()->id();

        $loan = Loan::create($data);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($loan)
            ->log("Recorded loan from {$loan->source_name} — ₹{$loan->principal_amount}");

        return redirect()->route('loans.show', $loan)
            ->with('success', 'Loan recorded.');
    }

    public function show(Loan $loan): View
    {
        $loan->load('repayments.creator', 'creator');
        return view('loans.show', compact('loan'));
    }

    public function edit(Loan $loan): View|RedirectResponse
    {
        if ($loan->status->value === 'repaid') {
            return redirect()->route('loans.show', $loan)
                ->with('error', 'Fully repaid loans cannot be edited.');
        }

        $sourceTypes = LoanSourceType::cases();
        return view('loans.edit', compact('loan', 'sourceTypes'));
    }

    public function update(Request $request, Loan $loan): RedirectResponse
    {
        $data = $request->validate([
            'source_name'      => ['required', 'string', 'max:255'],
            'source_type'      => ['required', 'in:person,bank,other'],
            'principal_amount' => ['required', 'numeric', 'min:0.01'],
            'borrowed_date'    => ['required', 'date'],
            'due_date'         => ['nullable', 'date', 'after_or_equal:borrowed_date'],
            'purpose'          => ['nullable', 'string', 'max:255'],
            'notes'            => ['nullable', 'string', 'max:2000'],
        ]);

        $loan->update($data);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($loan)
            ->log("Updated loan from {$loan->source_name}");

        return redirect()->route('loans.show', $loan)
            ->with('success', 'Loan updated.');
    }

    public function destroy(Loan $loan): RedirectResponse
    {
        if ($loan->repayments()->count() > 0) {
            return back()->with('error', 'Cannot delete a loan that has repayments. Remove all repayments first.');
        }

        activity()
            ->causedBy(auth()->user())
            ->withProperties(['source' => $loan->source_name])
            ->log('Deleted loan record');

        $loan->delete();

        return redirect()->route('loans.index')
            ->with('success', 'Loan deleted.');
    }

    public function recordRepayment(Request $request, Loan $loan): RedirectResponse
    {
        $outstanding = $loan->amountOutstanding();

        $data = $request->validate([
            'amount'      => ['required', 'numeric', 'min:0.01', 'max:' . $outstanding],
            'repaid_date' => ['required', 'date'],
            'reference'   => ['nullable', 'string', 'max:100'],
            'notes'       => ['nullable', 'string', 'max:1000'],
        ]);

        $data['loan_id']    = $loan->id;
        $data['created_by'] = auth()->id();

        LoanRepayment::create($data);

        $loan->syncStatus();

        activity()
            ->causedBy(auth()->user())
            ->performedOn($loan)
            ->log("Recorded repayment of ₹{$data['amount']} on loan from {$loan->source_name}");

        return back()->with('success', 'Repayment recorded.');
    }

    public function deleteRepayment(Loan $loan, LoanRepayment $repayment): RedirectResponse
    {
        if ($repayment->loan_id !== $loan->id) {
            abort(404);
        }

        $repayment->delete();

        $loan->syncStatus();

        activity()
            ->causedBy(auth()->user())
            ->performedOn($loan)
            ->log("Deleted repayment of ₹{$repayment->amount} from loan {$loan->source_name}");

        return back()->with('success', 'Repayment deleted.');
    }
}
