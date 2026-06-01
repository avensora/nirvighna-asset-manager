<?php

namespace App\Http\Controllers;

use App\Enums\ProjectPriority;
use App\Enums\ProjectStatus;
use App\Enums\UserRole;
use App\Models\Client;
use App\Models\Project;
use App\Models\Transaction;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        $query = Project::with(['client', 'creator', 'assignees'])
            ->when(! $user->isManager(), function ($q) use ($user) {
                $q->whereHas('assignments', fn ($a) => $a->where('user_id', $user->id));
            });

        // Filters
        if (request('status')) {
            $query->where('status', request('status'));
        }
        if (request('priority')) {
            $query->where('priority', request('priority'));
        }
        if (request('search')) {
            $query->where('title', 'like', '%' . request('search') . '%');
        }

        $projects  = $query->orderByRaw("FIELD(status,'active','planning','on_hold','completed','cancelled')")
                           ->orderBy('deadline')
                           ->paginate(20)
                           ->withQueryString();

        $statuses   = ProjectStatus::cases();
        $priorities = ProjectPriority::cases();

        return view('projects.index', compact('projects', 'statuses', 'priorities'));
    }

    public function create(): View
    {
        $clients   = Client::orderBy('name')->get();
        $teamLeads = User::where('role', UserRole::TeamLead)->where('is_active', true)->orderBy('name')->get();

        return view('projects.create', compact('clients', 'teamLeads'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'client_id'    => 'nullable|exists:clients,id',
            'status'       => 'required|in:planning,active,on_hold,completed,cancelled',
            'priority'     => 'required|in:low,medium,high,urgent',
            'start_date'   => 'nullable|date',
            'deadline'     => 'nullable|date|after_or_equal:start_date',
            'budget'       => 'nullable|numeric|min:0',
            'notes'        => 'nullable|string',
            'assignee_ids' => 'nullable|array',
            'assignee_ids.*' => 'exists:users,id',
        ]);

        $project = Project::create([
            'title'       => $data['title'],
            'description' => $data['description'] ?? null,
            'client_id'   => $data['client_id'] ?? null,
            'status'      => $data['status'],
            'priority'    => $data['priority'],
            'start_date'  => $data['start_date'] ?? null,
            'deadline'    => $data['deadline'] ?? null,
            'budget'      => $data['budget'] ?? null,
            'notes'       => $data['notes'] ?? null,
            'created_by'  => auth()->id(),
        ]);

        // Assign team leads
        foreach ($data['assignee_ids'] ?? [] as $userId) {
            $project->assignments()->create([
                'user_id'     => $userId,
                'role'        => 'lead',
                'assigned_at' => now(),
            ]);

            NotificationService::notify(
                $userId,
                'project_assigned',
                "Assigned to project: {$project->title}",
                auth()->user()->name . " assigned you to the project \"{$project->title}\".",
                ['project_id' => $project->id]
            );
        }

        activity()
            ->causedBy(auth()->user())
            ->performedOn($project)
            ->withProperties(['title' => $project->title, 'status' => $data['status'], 'priority' => $data['priority']])
            ->log('Created project');

        return redirect()->route('projects.show', $project)
            ->with('success', "Project \"{$project->title}\" created.");
    }

    public function show(Project $project): View
    {
        $user = auth()->user();

        if (! $user->isManager() && ! $project->isAssigned($user->id)) {
            abort(403, 'You are not assigned to this project.');
        }

        $project->load(['client', 'creator', 'assignees', 'invoices.client', 'calendarEvents']);

        $activities = \Spatie\Activitylog\Models\Activity::where('subject_type', Project::class)
            ->where('subject_id', $project->id)
            ->with('causer')
            ->latest()
            ->take(25)
            ->get();

        $projectIncome  = null;
        $projectExpense = null;
        if ($user->isManager()) {
            $projectIncome  = Transaction::where('project_id', $project->id)->where('type', 'income') ->latest('date')->get();
            $projectExpense = Transaction::where('project_id', $project->id)->where('type', 'expense')->latest('date')->get();
        }

        return view('projects.show', compact('project', 'activities', 'projectIncome', 'projectExpense'));
    }

    public function edit(Project $project): View
    {
        $clients   = Client::orderBy('name')->get();
        $teamLeads = User::where('role', UserRole::TeamLead)->where('is_active', true)->orderBy('name')->get();
        $assignedIds = $project->assignments()->pluck('user_id')->toArray();

        return view('projects.edit', compact('project', 'clients', 'teamLeads', 'assignedIds'));
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $data = $request->validate([
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'client_id'    => 'nullable|exists:clients,id',
            'status'       => 'required|in:planning,active,on_hold,completed,cancelled',
            'priority'     => 'required|in:low,medium,high,urgent',
            'start_date'   => 'nullable|date',
            'deadline'     => 'nullable|date|after_or_equal:start_date',
            'budget'       => 'nullable|numeric|min:0',
            'notes'        => 'nullable|string',
            'assignee_ids' => 'nullable|array',
            'assignee_ids.*' => 'exists:users,id',
        ]);

        $project->update([
            'title'       => $data['title'],
            'description' => $data['description'] ?? null,
            'client_id'   => $data['client_id'] ?? null,
            'status'      => $data['status'],
            'priority'    => $data['priority'],
            'start_date'  => $data['start_date'] ?? null,
            'deadline'    => $data['deadline'] ?? null,
            'budget'      => $data['budget'] ?? null,
            'notes'       => $data['notes'] ?? null,
        ]);

        // Sync assignments
        $project->assignments()->delete();
        foreach ($data['assignee_ids'] ?? [] as $userId) {
            $project->assignments()->create([
                'user_id'     => $userId,
                'role'        => 'lead',
                'assigned_at' => now(),
            ]);
        }

        activity()
            ->causedBy(auth()->user())
            ->performedOn($project)
            ->withProperties(['title' => $project->title])
            ->log('Updated project');

        return redirect()->route('projects.show', $project)
            ->with('success', "Project updated.");
    }

    public function destroy(Project $project): RedirectResponse
    {
        $title = $project->title;
        $project->delete();

        activity()
            ->causedBy(auth()->user())
            ->withProperties(['title' => $title])
            ->log('Deleted project');

        return redirect()->route('projects.index')
            ->with('success', "Project \"{$title}\" deleted.");
    }

    public function updateProgress(Request $request, Project $project): RedirectResponse
    {
        $user = auth()->user();

        if (! $user->isManager() && ! $project->isAssigned($user->id)) {
            abort(403, 'You are not assigned to this project.');
        }

        $data = $request->validate([
            'progress' => 'required|integer|min:0|max:100',
            'notes'    => 'nullable|string|max:1000',
        ]);

        $old = $project->progress;
        $project->update(['progress' => $data['progress']]);

        $note = $data['notes'] ? ' — ' . $data['notes'] : '';
        activity()
            ->causedBy($user)
            ->performedOn($project)
            ->withProperties(['from' => $old, 'to' => $data['progress']])
            ->log("Progress updated to {$data['progress']}%{$note}");

        // Notify project creator if they are not the one updating
        if ($project->created_by && $project->created_by !== $user->id) {
            NotificationService::notify(
                $project->created_by,
                'project_progress',
                "Progress update: {$project->title}",
                "{$user->name} updated progress to {$data['progress']}%{$note}.",
                ['project_id' => $project->id]
            );
        }

        return back()->with('success', "Progress updated to {$data['progress']}%.");
    }
}
