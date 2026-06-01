<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TeamController extends Controller
{
    public function index(): View
    {
        $members = User::orderBy('name')->get();
        return view('team.index', compact('members'));
    }

    public function create(): View
    {
        return view('team.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'role'  => 'required|in:manager,team_member',
        ]);

        $user = User::create([
            'name'              => $data['name'],
            'email'             => $data['email'],
            'role'              => $data['role'],
            'password'          => bcrypt(Str::random(32)),
            'email_verified_at' => now(),
            'is_active'         => true,
        ]);

        // Send password-reset link so the invitee can set their own password
        try {
            Password::sendResetLink(['email' => $user->email]);
            $emailNote = " A password setup email has been sent to {$user->email}.";
        } catch (\Exception $e) {
            $emailNote = " (Mail server unavailable — share the password reset link manually.)";
        }

        activity()
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->withProperties(['name' => $user->name, 'email' => $user->email, 'role' => $data['role']])
            ->log('Invited team member');

        return redirect()->route('team.index')
            ->with('success', "{$user->name} invited.{$emailNote}");
    }

    public function edit(User $user): View
    {
        return view('team.edit', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'role'  => 'required|in:manager,team_member',
        ]);

        // Guard: can't change own role or deactivate self
        if ($user->id === auth()->id()) {
            if ($data['role'] !== 'manager') {
                return back()->with('error', 'You cannot change your own role.');
            }
            if (!$request->boolean('is_active')) {
                return back()->with('error', 'You cannot deactivate your own account.');
            }
        }

        // Guard: can't remove last active manager
        $isBeingDemoted     = $user->role === UserRole::Manager && $data['role'] !== 'manager';
        $isBeingDeactivated = $user->is_active && !$request->boolean('is_active');
        if ($user->role === UserRole::Manager && ($isBeingDemoted || $isBeingDeactivated)) {
            $activeManagerCount = User::where('role', UserRole::Manager)->where('is_active', true)->count();
            if ($activeManagerCount <= 1) {
                return back()->with('error', 'Cannot demote or deactivate the last active manager.');
            }
        }

        $user->update([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'role'      => $data['role'],
            'is_active' => $request->boolean('is_active'),
        ]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->withProperties(['name' => $user->name])
            ->log('Updated team member');

        return redirect()->route('team.index')
            ->with('success', "{$user->name} updated successfully.");
    }
}
