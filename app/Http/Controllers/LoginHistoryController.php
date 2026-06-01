<?php

namespace App\Http\Controllers;

use App\Models\LoginHistory;
use Illuminate\Http\Request;

class LoginHistoryController extends Controller
{
    public function index(Request $request)
    {
        $user    = auth()->user();
        $history = LoginHistory::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(25);

        return view('profile.login-history', compact('history'));
    }
}
