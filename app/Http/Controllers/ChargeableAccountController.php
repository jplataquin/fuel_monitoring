<?php

namespace App\Http\Controllers;

use App\Models\ChargeableAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChargeableAccountController extends Controller
{
    public function index(): View
    {
        $chargeableAccounts = ChargeableAccount::all();

        return view('chargeable-accounts.index', compact('chargeableAccounts'));
    }

    public function create(): View
    {
        return view('chargeable-accounts.create');
    }

    public function show(ChargeableAccount $chargeableAccount): View
    {
        $chargeableAccount->load('subAccounts');

        return view('chargeable-accounts.show', compact('chargeableAccount'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:chargeable_accounts',
            'classification' => 'required|in:Running,Scoped',
            'start_date' => 'required_if:classification,Scoped|nullable|date',
            'end_date' => 'required_if:classification,Scoped|nullable|date|after_or_equal:start_date',
            'status' => 'required|in:Active,Inactive',
        ]);

        if ($validated['classification'] === 'Running') {
            $validated['start_date'] = null;
            $validated['end_date'] = null;
        }

        ChargeableAccount::create($validated);

        return redirect()->route('chargeable-accounts.index')->with('status', 'Chargeable account created successfully.');
    }

    public function edit(ChargeableAccount $chargeableAccount): View
    {
        return view('chargeable-accounts.edit', compact('chargeableAccount'));
    }

    public function update(Request $request, ChargeableAccount $chargeableAccount): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:chargeable_accounts,name,'.$chargeableAccount->id,
            'classification' => 'required|in:Running,Scoped',
            'start_date' => 'required_if:classification,Scoped|nullable|date',
            'end_date' => 'required_if:classification,Scoped|nullable|date|after_or_equal:start_date',
            'status' => 'required|in:Active,Inactive',
        ]);

        if ($validated['classification'] === 'Running') {
            $validated['start_date'] = null;
            $validated['end_date'] = null;
        }

        $chargeableAccount->update($validated);

        return redirect()->route('chargeable-accounts.index')->with('status', 'Chargeable account updated successfully.');
    }

    public function destroy(ChargeableAccount $chargeableAccount): RedirectResponse
    {
        $chargeableAccount->delete();

        return redirect()->route('chargeable-accounts.index')->with('status', 'Chargeable account deleted successfully.');
    }
}
