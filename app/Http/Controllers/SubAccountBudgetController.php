<?php

namespace App\Http\Controllers;

use App\Models\SubAccount;
use App\Models\SubAccountBudget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubAccountBudgetController extends Controller
{
    public function index(Request $request)
    {
        $query = SubAccountBudget::query()
                    ->select('sub_account_budgets.*', 'sub_accounts.chargeable_account_id')
                    ->join('sub_accounts', 'sub_account_budgets.sub_account_id', '=', 'sub_accounts.id')
                    ->join('chargeable_accounts', 'sub_accounts.chargeable_account_id', '=', 'chargeable_accounts.id')
                    ->with(['subAccount.chargeableAccount', 'creator'])
                    ->orderBy('chargeable_accounts.name', 'asc')
                    ->orderBy('sub_accounts.name', 'asc')
                    ->orderBy('sub_account_budgets.created_at', 'desc');

        if ($request->filled('chargeable_account_id')) {
            $query->where('sub_accounts.chargeable_account_id', $request->chargeable_account_id);
            $budgets = $query->paginate(50)->withQueryString();
        } else {
            // Only show result if user has selected a filter
            $budgets = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 50);
        }

        $accounts = \App\Models\ChargeableAccount::orderBy('name')->get();

        return view('sub-account-budgets.index', compact('budgets', 'accounts'));
    }

    public function show(SubAccountBudget $accountBudget)
    {
        $accountBudget->load(['subAccount', 'creator', 'updater', 'approver', 'rejecter']);
        return view('sub-account-budgets.show', compact('accountBudget'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sub_account_id' => 'required|exists:sub_accounts,id',
            'budget_quantity' => 'required|numeric|min:0',
            'remarks' => 'nullable|string',
        ]);

        $validated['status'] = 'Pending';
        $validated['created_by'] = Auth::id();

        SubAccountBudget::create($validated);

        return redirect()->route('sub-accounts.show', $validated['sub_account_id'])
            ->with('status', 'Budget allocated successfully and is pending approval.');
    }

    public function edit(SubAccountBudget $accountBudget)
    {
        if (Auth::user()->role === 'budgeteer' && $accountBudget->status !== 'Pending') {
            abort(403, 'You can only edit budgets that are in pending status.');
        }

        $accounts = SubAccount::orderBy('name')->get();
        return view('sub-account-budgets.edit', compact('accountBudget', 'accounts'));
    }

    public function update(Request $request, SubAccountBudget $accountBudget)
    {
        if (Auth::user()->role === 'budgeteer' && $accountBudget->status !== 'Pending') {
            abort(403, 'You can only update budgets that are in pending status.');
        }

        $rules = [
            'sub_account_id' => 'required|exists:sub_accounts,id',
            'budget_quantity' => 'required|numeric|min:0',
            'remarks' => 'nullable|string',
        ];

        if (in_array(Auth::user()->role, ['administrator', 'moderator'])) {
            $rules['status'] = 'required|in:Pending,Approved,Rejected';
        }

        $validated = $request->validate($rules);

        if (isset($validated['status']) && $validated['status'] !== $accountBudget->status) {
            if ($validated['status'] === 'Approved') {
                $validated['approved_by'] = Auth::id();
                $validated['approved_at'] = now();
                $validated['rejected_by'] = null;
                $validated['rejected_at'] = null;
            } elseif ($validated['status'] === 'Rejected') {
                $validated['rejected_by'] = Auth::id();
                $validated['rejected_at'] = now();
                $validated['approved_by'] = null;
                $validated['approved_at'] = null;
            } else {
                // Pending
                $validated['approved_by'] = null;
                $validated['approved_at'] = null;
                $validated['rejected_by'] = null;
                $validated['rejected_at'] = null;
            }
        }

        $validated['updated_by'] = Auth::id();

        $accountBudget->update($validated);

        return redirect()->route('account-budgets.index')
            ->with('status', 'Budget updated successfully.');
    }

    public function destroy(SubAccountBudget $accountBudget)
    {
        if (Auth::user()->role === 'budgeteer' && $accountBudget->status !== 'Pending') {
            abort(403, 'You can only delete budgets that are in pending status.');
        }

        $accountBudget->update(['deleted_by' => Auth::id()]);
        $accountBudget->delete();

        return redirect()->route('account-budgets.index')
            ->with('status', 'Budget soft deleted successfully.');
    }

    public function approve(SubAccountBudget $accountBudget)
    {
        if (!in_array(Auth::user()->role, ['administrator', 'moderator'])) {
            abort(403);
        }

        $accountBudget->update([
            'status' => 'Approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'rejected_by' => null,
            'rejected_at' => null,
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('account-budgets.index')
            ->with('status', 'Budget approved successfully.');
    }

    public function reject(SubAccountBudget $accountBudget)
    {
        if (!in_array(Auth::user()->role, ['administrator', 'moderator'])) {
            abort(403);
        }

        $accountBudget->update([
            'status' => 'Rejected',
            'rejected_by' => Auth::id(),
            'rejected_at' => now(),
            'approved_by' => null,
            'approved_at' => null,
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('account-budgets.index')
            ->with('status', 'Budget rejected successfully.');
    }
}
