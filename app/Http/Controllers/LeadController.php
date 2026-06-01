<?php

namespace App\Http\Controllers;

use App\Enums\LeadStage;
use App\Enums\ProjectPriority;
use App\Enums\ProjectStatus;
use App\Models\Client;
use App\Models\Lead;
use App\Models\Project;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeadController extends Controller
{
    public function index(): View
    {
        $query = Lead::with(['assignee', 'creator']);

        if (request('stage')) {
            $query->where('stage', request('stage'));
        }
        if (request('search')) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . request('search') . '%')
                  ->orWhere('company', 'like', '%' . request('search') . '%')
                  ->orWhere('email', 'like', '%' . request('search') . '%');
            });
        }

        $view = request('view', 'table');

        if ($view === 'kanban') {
            $leads   = $query->orderBy('updated_at', 'desc')->get();
            $grouped = collect(LeadStage::pipeline())->mapWithKeys(
                fn ($stage) => [$stage->value => $leads->where('stage', $stage)]
            );
            $stages = LeadStage::pipeline();
            return view('leads.index', compact('leads', 'grouped', 'stages', 'view'));
        }

        $leads  = $query->orderBy('created_at', 'desc')->paginate(25)->withQueryString();
        $stages = LeadStage::pipeline();

        return view('leads.index', compact('leads', 'stages', 'view'));
    }

    public function create(): View
    {
        $managers = User::whereIn('role', ['manager', 'master_admin'])->where('is_active', true)->orderBy('name')->get();
        $stages   = LeadStage::pipeline();
        return view('leads.create', compact('managers', 'stages'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'nullable|email|max:255',
            'phone'       => 'nullable|string|max:30',
            'company'     => 'nullable|string|max:255',
            'source'      => 'nullable|string|max:100',
            'deal_value'  => 'nullable|numeric|min:0',
            'stage'       => 'required|in:new_lead,contacted,interested,proposal_sent,won,lost',
            'notes'       => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $lead = Lead::create([...$data, 'created_by' => auth()->id()]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($lead)
            ->withProperties(['name' => $lead->name, 'stage' => $data['stage']])
            ->log('Created lead');

        return redirect()->route('leads.show', $lead)
            ->with('success', "Lead \"{$lead->name}\" created.");
    }

    public function show(Lead $lead): View
    {
        $lead->load(['assignee', 'creator']);

        $activities = \Spatie\Activitylog\Models\Activity::where('subject_type', Lead::class)
            ->where('subject_id', $lead->id)
            ->with('causer')
            ->latest()
            ->take(20)
            ->get();

        $stages = LeadStage::pipeline();

        return view('leads.show', compact('lead', 'activities', 'stages'));
    }

    public function edit(Lead $lead): View
    {
        $managers = User::whereIn('role', ['manager', 'master_admin'])->where('is_active', true)->orderBy('name')->get();
        $stages   = LeadStage::pipeline();
        return view('leads.edit', compact('lead', 'managers', 'stages'));
    }

    public function update(Request $request, Lead $lead): RedirectResponse
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'nullable|email|max:255',
            'phone'       => 'nullable|string|max:30',
            'company'     => 'nullable|string|max:255',
            'source'      => 'nullable|string|max:100',
            'deal_value'  => 'nullable|numeric|min:0',
            'stage'       => 'required|in:new_lead,contacted,interested,proposal_sent,won,lost',
            'notes'       => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $lead->update($data);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($lead)
            ->withProperties(['name' => $lead->name])
            ->log('Updated lead');

        return redirect()->route('leads.show', $lead)
            ->with('success', "Lead updated.");
    }

    public function destroy(Lead $lead): RedirectResponse
    {
        $name = $lead->name;
        $lead->delete();

        activity()
            ->causedBy(auth()->user())
            ->withProperties(['name' => $name])
            ->log('Deleted lead');

        return redirect()->route('leads.index')
            ->with('success', "Lead \"{$name}\" deleted.");
    }

    public function updateStage(Request $request, Lead $lead): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'stage' => 'required|in:new_lead,contacted,interested,proposal_sent,won,lost',
        ]);

        $old = $lead->stage->label();
        $lead->update(['stage' => $data['stage']]);
        $new = $lead->stage->label();

        activity()
            ->causedBy(auth()->user())
            ->performedOn($lead)
            ->withProperties(['from' => $old, 'to' => $new])
            ->log("Stage moved: {$old} → {$new}");

        // Notify the assigned user (if any and not the one making the change)
        if ($lead->assigned_to && $lead->assigned_to !== auth()->id()) {
            NotificationService::notify(
                $lead->assigned_to,
                'lead_stage',
                "Lead stage updated: {$lead->name}",
                auth()->user()->name . " moved \"{$lead->name}\" from {$old} to {$new}.",
                ['lead_id' => $lead->id]
            );
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', "Stage updated to {$new}.");
    }

    public function convertToProject(Lead $lead): RedirectResponse
    {
        // Try to match an existing client by email or company name
        $client = null;
        if ($lead->email) {
            $client = Client::where('email', $lead->email)->first();
        }
        if (! $client && $lead->company) {
            $client = Client::where('name', $lead->company)
                ->orWhere('company', $lead->company)
                ->first();
        }

        $project = Project::create([
            'title'      => $lead->company ? "{$lead->company} — {$lead->name}" : $lead->name,
            'client_id'  => $client?->id,
            'status'     => ProjectStatus::Planning->value,
            'priority'   => ProjectPriority::Medium->value,
            'budget'     => $lead->deal_value,
            'notes'      => $lead->notes,
            'created_by' => auth()->id(),
        ]);

        // Mark lead as Won if not already
        if ($lead->stage !== LeadStage::Won) {
            $lead->update(['stage' => LeadStage::Won->value]);
        }

        activity()
            ->causedBy(auth()->user())
            ->performedOn($lead)
            ->withProperties(['project_id' => $project->id, 'project_title' => $project->title])
            ->log("Converted to project: {$project->title}");

        return redirect()->route('projects.show', $project)
            ->with('success', "Lead converted to project \"{$project->title}\".");
    }
}
