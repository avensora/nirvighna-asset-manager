<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompanySettingController extends Controller
{
    public function edit(): View
    {
        $openingBalance     = CompanySetting::get('opening_balance', '0');
        $openingBalanceDate = CompanySetting::get('opening_balance_date', now()->toDateString());

        return view('settings.company', compact('openingBalance', 'openingBalanceDate'));
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'opening_balance'      => ['required', 'numeric', 'min:0'],
            'opening_balance_date' => ['required', 'date'],
        ]);

        CompanySetting::set('opening_balance', $data['opening_balance']);
        CompanySetting::set('opening_balance_date', $data['opening_balance_date']);

        activity()
            ->causedBy(auth()->user())
            ->log("Updated company opening balance to ₹{$data['opening_balance']} from {$data['opening_balance_date']}");

        return back()->with('success', 'Opening balance updated.');
    }
}
