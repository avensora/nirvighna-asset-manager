<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class ActivityController extends Controller
{
    private const SUBJECT_MAP = [
        'Client'      => 'App\Models\Client',
        'Invoice'     => 'App\Models\Invoice',
        'Transaction' => 'App\Models\Transaction',
    ];

    public function index(Request $request): View
    {
        $query = Activity::with('causer')
            ->latest();

        if ($request->filled('subject_type') && isset(self::SUBJECT_MAP[$request->subject_type])) {
            $query->where('subject_type', self::SUBJECT_MAP[$request->subject_type]);
        }

        if ($request->filled('causer_id')) {
            $query->where('causer_type', 'App\Models\User')
                  ->where('causer_id', $request->causer_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $activities = $query->paginate(25)->withQueryString();
        $users       = User::orderBy('name')->get();

        return view('activity.index', compact('activities', 'users'));
    }
}
