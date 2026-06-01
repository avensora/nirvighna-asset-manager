<?php

namespace App\Http\Controllers;

use App\Enums\RecurrenceType;
use App\Enums\TransactionType;
use App\Models\ScheduledExpense;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScheduledExpenseController extends Controller
{
    public function index(): View
    {
        $expenses = ScheduledExpense::with('creator')
            ->orderByRaw("FIELD(status, 'pending', 'paid')")
            ->orderBy('due_date')
            ->get();

        return view('scheduled-expenses.index', compact('expenses'));
    }

    public function create(): View
    {
        return view('scheduled-expenses.create', [
            'recurrenceTypes' => RecurrenceType::cases(),
            'categories'      => \App\Enums\TransactionType::categories(TransactionType::Expense),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'      => 'required|string|max:255',
            'amount'     => 'required|numeric|min:0.01',
            'category'   => 'nullable|string|max:100',
            'due_date'   => 'required|date',
            'recurrence' => 'required|in:none,monthly,quarterly,yearly',
            'notes'      => 'nullable|string',
        ]);

        $data['created_by'] = auth()->id();
        $data['status']     = 'pending';

        $expense = ScheduledExpense::create($data);

        activity()->on($expense)->causedBy(auth()->user())
            ->log('Created scheduled expense: ' . $expense->title);

        return redirect()->route('scheduled-expenses.index')
            ->with('success', 'Scheduled expense created.');
    }

    public function edit(ScheduledExpense $scheduledExpense): View
    {
        $this->authorise($scheduledExpense);

        return view('scheduled-expenses.edit', [
            'expense'         => $scheduledExpense,
            'recurrenceTypes' => RecurrenceType::cases(),
            'categories'      => \App\Enums\TransactionType::categories(TransactionType::Expense),
        ]);
    }

    public function update(Request $request, ScheduledExpense $scheduledExpense): RedirectResponse
    {
        $this->authorise($scheduledExpense);

        $data = $request->validate([
            'title'      => 'required|string|max:255',
            'amount'     => 'required|numeric|min:0.01',
            'category'   => 'nullable|string|max:100',
            'due_date'   => 'required|date',
            'recurrence' => 'required|in:none,monthly,quarterly,yearly',
            'notes'      => 'nullable|string',
        ]);

        $scheduledExpense->update($data);

        activity()->on($scheduledExpense)->causedBy(auth()->user())
            ->log('Updated scheduled expense: ' . $scheduledExpense->title);

        return redirect()->route('scheduled-expenses.index')
            ->with('success', 'Scheduled expense updated.');
    }

    public function destroy(ScheduledExpense $scheduledExpense): RedirectResponse
    {
        $this->authorise($scheduledExpense);

        activity()->causedBy(auth()->user())
            ->log('Deleted scheduled expense: ' . $scheduledExpense->title);

        $scheduledExpense->delete();

        return redirect()->route('scheduled-expenses.index')
            ->with('success', 'Scheduled expense deleted.');
    }

    public function pay(ScheduledExpense $scheduledExpense): RedirectResponse
    {
        if ($scheduledExpense->status === 'paid') {
            return redirect()->route('scheduled-expenses.index')
                ->with('error', 'This expense is already marked as paid.');
        }

        Transaction::create([
            'type'        => TransactionType::Expense,
            'category'    => $scheduledExpense->category ?? 'Other Expense',
            'amount'      => $scheduledExpense->amount,
            'date'        => today(),
            'description' => $scheduledExpense->title . ($scheduledExpense->notes ? ' — ' . $scheduledExpense->notes : ''),
            'user_id'     => auth()->id(),
        ]);

        if ($scheduledExpense->recurrence === RecurrenceType::None) {
            $scheduledExpense->update([
                'status'       => 'paid',
                'last_paid_at' => today(),
            ]);
        } else {
            $scheduledExpense->update([
                'due_date'     => $scheduledExpense->recurrence->nextDueDate($scheduledExpense->due_date),
                'last_paid_at' => today(),
            ]);
        }

        activity()->on($scheduledExpense)->causedBy(auth()->user())
            ->log('Paid scheduled expense: ' . $scheduledExpense->title);

        return redirect()->route('scheduled-expenses.index')
            ->with('success', 'Marked as paid and transaction recorded.');
    }

    private function authorise(ScheduledExpense $expense): void
    {
        $user = auth()->user();
        if (! $user->isManager() && $expense->created_by !== $user->id) {
            abort(403);
        }
    }
}
