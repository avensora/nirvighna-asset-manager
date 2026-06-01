<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LoginHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = auth()->user();

            if (!$user->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                LoginHistory::create([
                    'user_id'    => $user->id,
                    'ip_address' => $request->ip(),
                    'user_agent' => substr($request->userAgent() ?? '', 0, 255),
                    'status'     => 'failed',
                ]);

                return back()->withErrors([
                    'email' => 'This account has been deactivated. Contact your manager.',
                ])->onlyInput('email');
            }

            LoginHistory::create([
                'user_id'    => $user->id,
                'ip_address' => $request->ip(),
                'user_agent' => substr($request->userAgent() ?? '', 0, 255),
                'status'     => 'success',
            ]);

            activity()->causedBy($user)->log('logged in');

            if ($user->hasTwoFactorEnabled()) {
                $request->session()->put('2fa_pending', true);
                return redirect()->route('2fa.challenge');
            }

            return redirect()->intended(route('dashboard'));
        }

        // Log failed attempt if user exists
        $failedUser = \App\Models\User::where('email', $request->email)->first();
        if ($failedUser) {
            LoginHistory::create([
                'user_id'    => $failedUser->id,
                'ip_address' => $request->ip(),
                'user_agent' => substr($request->userAgent() ?? '', 0, 255),
                'status'     => 'failed',
            ]);
        }

        return back()->withErrors([
            'email' => 'These credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        activity()
            ->causedBy(auth()->user())
            ->log('logged out');

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
