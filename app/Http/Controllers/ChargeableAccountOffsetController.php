<?php

namespace App\Http\Controllers;

use App\Models\ChargeableAccount;
use App\Models\ChargeableAccountOffset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChargeableAccountOffsetController extends Controller
{
    public function store(Request $request, ChargeableAccount $chargeableAccount): RedirectResponse
    {
        if (Auth::user()->role !== 'administrator') {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'quantity' => 'required|numeric|min:0',
            'remarks' => 'nullable|string|max:255',
        ]);

        $validated['created_by'] = Auth::id();

        $chargeableAccount->offsets()->create($validated);

        return redirect()->route('chargeable-accounts.show', $chargeableAccount)
            ->with('status', 'Budget offset added successfully.');
    }

    public function destroy(ChargeableAccount $chargeableAccount, ChargeableAccountOffset $offset): RedirectResponse
    {
        if (Auth::user()->role !== 'administrator') {
            abort(403, 'Unauthorized action.');
        }

        if ($offset->chargeable_account_id !== $chargeableAccount->id) {
            abort(404);
        }

        $offset->delete();

        return redirect()->route('chargeable-accounts.show', $chargeableAccount)
            ->with('status', 'Budget offset deleted successfully.');
    }
}
