<?php

namespace App\Http\Controllers;

use App\Models\ChargeableAccount;
use App\Models\SubAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

use Illuminate\Validation\Rule;

use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class SubAccountController extends Controller
{
    public function byAccount(ChargeableAccount $chargeableAccount): JsonResponse
    {
        return response()->json($chargeableAccount->subAccounts);
    }

    public function show(SubAccount $subAccount): View
    {
        return view('sub-accounts.show', compact('subAccount'));
    }

    public function edit(SubAccount $subAccount): View
    {
        return view('sub-accounts.edit', compact('subAccount'));
    }

    public function update(Request $request, SubAccount $subAccount): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('sub_accounts')
                    ->where(fn ($query) => $query->where('chargeable_account_id', $subAccount->chargeable_account_id))
                    ->ignore($subAccount->id)
                    ->whereNull('deleted_at'),
            ],
        ]);

        $subAccount->update($validated);

        return redirect()->route('chargeable-accounts.show', $subAccount->chargeableAccount)->with('status', 'Sub-account updated successfully.');
    }

    public function store(Request $request, ChargeableAccount $chargeableAccount): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('sub_accounts')
                    ->where(fn ($query) => $query->where('chargeable_account_id', $chargeableAccount->id))
                    ->whereNull('deleted_at'),
            ],
        ]);

        $chargeableAccount->subAccounts()->create($validated);

        return redirect()->route('chargeable-accounts.show', $chargeableAccount)->with('status', 'Sub-account added successfully.');
    }

    public function destroy(SubAccount $subAccount): RedirectResponse
    {
        $chargeableAccount = $subAccount->chargeableAccount;
        $subAccount->delete();

        return redirect()->route('chargeable-accounts.show', $chargeableAccount)->with('status', 'Sub-account deleted successfully.');
    }
}
