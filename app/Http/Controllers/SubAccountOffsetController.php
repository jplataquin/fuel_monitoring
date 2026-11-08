<?php

namespace App\Http\Controllers;

use App\Models\SubAccount;
use App\Models\SubAccountOffset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubAccountOffsetController extends Controller
{
    public function store(Request $request, SubAccount $subAccount)
    {
        $validated = $request->validate([
            'quantity' => 'required|numeric',
            'remarks' => 'nullable|string',
        ]);

        $validated['sub_account_id'] = $subAccount->id;
        $validated['status'] = 'Pending';
        $validated['created_by'] = Auth::id();

        SubAccountOffset::create($validated);

        return redirect()->route('sub-accounts.show', $subAccount)
            ->with('status', 'Offset registered successfully and is pending approval.');
    }

    public function destroy(SubAccountOffset $subAccountOffset)
    {
        if (Auth::user()->role === 'budgeteer' && $subAccountOffset->status !== 'Pending') {
            abort(403, 'You can only delete offsets that are in pending status.');
        }

        $subAccount = $subAccountOffset->subAccount;

        $subAccountOffset->update(['deleted_by' => Auth::id()]);
        $subAccountOffset->delete();

        return redirect()->route('sub-accounts.show', $subAccount)
            ->with('status', 'Offset deleted successfully.');
    }

    public function approve(SubAccountOffset $subAccountOffset)
    {
        if (! in_array(Auth::user()->role, ['administrator', 'moderator'])) {
            abort(403);
        }

        $subAccountOffset->update([
            'status' => 'Approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'rejected_by' => null,
            'rejected_at' => null,
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('sub-accounts.show', $subAccountOffset->subAccount)
            ->with('status', 'Offset approved successfully.');
    }

    public function reject(SubAccountOffset $subAccountOffset)
    {
        if (! in_array(Auth::user()->role, ['administrator', 'moderator'])) {
            abort(403);
        }

        $subAccountOffset->update([
            'status' => 'Rejected',
            'rejected_by' => Auth::id(),
            'rejected_at' => now(),
            'approved_by' => null,
            'approved_at' => null,
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('sub-accounts.show', $subAccountOffset->subAccount)
            ->with('status', 'Offset rejected successfully.');
    }
}
