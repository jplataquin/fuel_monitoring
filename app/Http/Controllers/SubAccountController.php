<?php

namespace App\Http\Controllers;

use App\Models\ChargeableAccount;
use App\Models\FuelOrder;
use App\Models\SubAccount;
use App\Models\SubAccountBudget;
use App\Models\UtilizationEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

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
                'not_regex:/[:]/',
            ],
            'accomplishment' => 'sometimes|numeric|min:0|max:100',
            'type' => 'nullable|in:Controlled,Uncontrolled',
        ], [
            'name.not_regex' => 'The Sub-Account Name cannot contain colons (:).',
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
                'not_regex:/[:]/',
            ],
        ], [
            'name.not_regex' => 'The Sub-Account Name cannot contain colons (:).',
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

    public function merge(Request $request, SubAccount $subAccount): RedirectResponse
    {
        if (Auth::user()->role !== 'administrator') {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'merged_to_id' => [
                'required',
                'exists:sub_accounts,id',
                Rule::exists('sub_accounts', 'id')
                    ->where('chargeable_account_id', $subAccount->chargeable_account_id)
                    ->whereNull('deleted_at'),
                Rule::notIn([$subAccount->id]),
            ],
            'merge_remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        $targetSubAccount = SubAccount::findOrFail($validated['merged_to_id']);

        DB::transaction(function () use ($subAccount, $targetSubAccount, $validated) {
            SubAccountBudget::where('sub_account_id', $subAccount->id)
                ->update(['sub_account_id' => $targetSubAccount->id]);

            UtilizationEntry::where('sub_account_id', $subAccount->id)
                ->update(['sub_account_id' => $targetSubAccount->id]);

            FuelOrder::where('sub_account_id', $subAccount->id)
                ->update(['sub_account_id' => $targetSubAccount->id]);

            $subAccount->update([
                'merged_to_id' => $targetSubAccount->id,
                'merged_by' => Auth::id(),
                'merged_at' => now(),
                'merge_remarks' => $validated['merge_remarks'] ?? null,
            ]);

            $subAccount->delete();
        });

        return redirect()->route('chargeable-accounts.show', $subAccount->chargeableAccount)
            ->with('status', sprintf('Sub-account "%s" has been successfully merged into "%s".', $subAccount->name, $targetSubAccount->name));
    }
}
