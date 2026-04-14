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
            'status' => 'required|in:Active,Inactive',
        ]);

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
            'status' => 'required|in:Active,Inactive',
        ]);

        $chargeableAccount->update($validated);

        return redirect()->route('chargeable-accounts.index')->with('status', 'Chargeable account updated successfully.');
    }

    public function destroy(ChargeableAccount $chargeableAccount): RedirectResponse
    {
        $chargeableAccount->delete();

        return redirect()->route('chargeable-accounts.index')->with('status', 'Chargeable account deleted successfully.');
    }
}
