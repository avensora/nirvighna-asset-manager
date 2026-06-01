<?php

namespace App\Http\Controllers;

use App\Enums\ReimbursementStatus;
use App\Enums\TransactionType;
use App\Models\Project;
use App\Models\Reimbursement;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReimbursementController extends Controller
{
    public function index(Request $request): View
    {
        $query = Reimbursement::with('user', 'project');

        if (! auth()->user()->isManager()) {
            $query->where('user_id', auth()->id());
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $reimbursements = $query->latest('spent_date')->paginate(20)->withQueryString();

        $pendingTotal    = Reimbursement::when(! auth()->user()->isManager(), fn ($q) => $q->where('user_id', auth()->id()))
            ->where('status', ReimbursementStatus::Pending)->sum('amount');
        $approvedTotal   = Reimbursement::when(! auth()->user()->isManager(), fn ($q) => $q->where('user_id', auth()->id()))
            ->where('status', ReimbursementStatus::Approved)->sum('amount');
        $reimbursedTotal = Reimbursement::when(! auth()->user()->isManager(), fn ($q) => $q->where('user_id', auth()->id()))
            ->where('status', ReimbursementStatus::Reimbursed)->sum('amount');

        return view('reimbursements.index', compact('reimbursements', 'pendingTotal', 'approvedTotal', 'reimbursedTotal'));
    }

    public function create(): View
    {
        $categories = TransactionType::categories(TransactionType::Expense);

        $projects = auth()->user()->isManager()
            ? Project::orderBy('title')->get()
            : Project::whereHas('assignees', fn ($q) => $q->where('users.id', auth()->id()))->orderBy('title')->get();

        return view('reimbursements.create', compact('categories', 'projects'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'amount'      => ['required', 'numeric', 'min:0.01'],
            'spent_date'  => ['required', 'date'],
            'category'    => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:2000'],
            'project_id'  => ['nullable', 'exists:projects,id'],
        ]);

        $data['user_id']    = auth()->id();
        $data['created_by'] = auth()->id();
        $data['status']     = ReimbursementStatus::Pending;

        $reimbursement = Reimbursement::create($data);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($reimbursement)
            ->log("Submitted reimbursement request: {$reimbursement->title} (₹{$reimbursement->amount})");

        return redirect()->route('reimbursements.index')
            ->with('success', 'Reimbursement request submitted.');
    }

    public function show(Reimbursement $reimbursement): View
    {
        if (! auth()->user()->isManager() && $reimbursement->user_id !== auth()->id()) {
            abort(403);
        }

        $reimbursement->load('user', 'approver', 'reimbursedBy', 'project');

        return view('reimbursements.show', compact('reimbursement'));
    }

    public function edit(Reimbursement $reimbursement): View|RedirectResponse
    {
        if (! auth()->user()->isManager() && $reimbursement->user_id !== auth()->id()) {
            abort(403);
        }

        if ($reimbursement->status !== ReimbursementStatus::Pending) {
            return redirect()->route('reimbursements.show', $reimbursement)
                ->with('error', 'Only pending reimbursements can be edited.');
        }

        $categories = TransactionType::categories(TransactionType::Expense);

        $projects = auth()->user()->isManager()
            ? Project::orderBy('title')->get()
            : Project::whereHas('assignees', fn ($q) => $q->where('users.id', auth()->id()))->orderBy('title')->get();

        return view('reimbursements.edit', compact('reimbursement', 'categories', 'projects'));
    }

    public function update(Request $request, Reimbursement $reimbursement): RedirectResponse
    {
        if (! auth()->user()->isManager() && $reimbursement->user_id !== auth()->id()) {
            abort(403);
        }

        if ($reimbursement->status !== ReimbursementStatus::Pending) {
            return redirect()->route('reimbursements.show', $reimbursement)
                ->with('error', 'Only pending reimbursements can be edited.');
        }

        $data = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'amount'      => ['required', 'numeric', 'min:0.01'],
            'spent_date'  => ['required', 'date'],
            'category'    => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:2000'],
            'project_id'  => ['nullable', 'exists:projects,id'],
        ]);

        $reimbursement->update($data);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($reimbursement)
            ->log("Updated reimbursement: {$reimbursement->title}");

        return redirect()->route('reimbursements.show', $reimbursement)
            ->with('success', 'Reimbursement updated.');
    }

    public function destroy(Reimbursement $reimbursement): RedirectResponse
    {
        if (! auth()->user()->isManager() && $reimbursement->user_id !== auth()->id()) {
            abort(403);
        }

        if ($reimbursement->status !== ReimbursementStatus::Pending) {
            return back()->with('error', 'Only pending reimbursements can be deleted.');
        }

        activity()
            ->causedBy(auth()->user())
            ->withProperties(['title' => $reimbursement->title])
            ->log('Deleted reimbursement request');

        $reimbursement->delete();

        return redirect()->route('reimbursements.index')
            ->with('success', 'Reimbursement request deleted.');
    }

    public function approve(Reimbursement $reimbursement): RedirectResponse
    {
        if ($reimbursement->status !== ReimbursementStatus::Pending) {
            return back()->with('error', 'Only pending reimbursements can be approved.');
        }

        $reimbursement->update([
            'status'      => ReimbursementStatus::Approved,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($reimbursement)
            ->log("Approved reimbursement: {$reimbursement->title}");

        return back()->with('success', 'Reimbursement approved.');
    }

    public function reject(Request $request, Reimbursement $reimbursement): RedirectResponse
    {
        $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        if (! in_array($reimbursement->status, [ReimbursementStatus::Pending, ReimbursementStatus::Approved])) {
            return back()->with('error', 'Cannot reject a reimbursement in its current state.');
        }

        $reimbursement->update([
            'status'           => ReimbursementStatus::Rejected,
            'rejection_reason' => $request->rejection_reason,
        ]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($reimbursement)
            ->log("Rejected reimbursement: {$reimbursement->title}");

        return back()->with('success', 'Reimbursement rejected.');
    }

    public function reimburse(Reimbursement $reimbursement): RedirectResponse
    {
        if ($reimbursement->status !== ReimbursementStatus::Approved) {
            return back()->with('error', 'Only approved reimbursements can be processed.');
        }

        Transaction::create([
            'type'        => TransactionType::Expense,
            'category'    => 'Employee Reimbursement',
            'amount'      => $reimbursement->amount,
            'date'        => today(),
            'description' => $reimbursement->title,
            'reference'   => 'RMB-' . $reimbursement->id,
            'user_id'     => auth()->id(),
        ]);

        $reimbursement->update([
            'status'         => ReimbursementStatus::Reimbursed,
            'reimbursed_by'  => auth()->id(),
            'reimbursed_at'  => now(),
        ]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($reimbursement)
            ->log("Reimbursed: {$reimbursement->title} (₹{$reimbursement->amount}) to {$reimbursement->user->name}");

        return back()->with('success', '₹' . number_format((float) $reimbursement->amount, 2) . " reimbursed to {$reimbursement->user->name}.");
    }
}
