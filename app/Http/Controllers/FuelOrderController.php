<?php

namespace App\Http\Controllers;

use App\Models\FuelOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FuelOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = FuelOrder::with(['asset', 'creator']);

        if ($request->filled('fleet_no')) {
            $query->whereHas('asset', function ($q) use ($request) {
                $q->where('fleet_no', 'like', '%' . $request->fleet_no . '%');
            });
        }

        $fuelOrders = $query->latest()->paginate(10)->withQueryString();

        return view('fuel-orders.index', compact('fuelOrders'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!in_array(Auth::user()->role, ['data_logger', 'data logger', 'administrator'])) {
            abort(403, 'Unauthorized action. Only Data Loggers can create fuel orders.');
        }

        return view('fuel-orders.create');
    }

    /**
     * Display the specified resource.
     */
    public function show(FuelOrder $fuelOrder)
    {
        $fuelOrder->load(['asset.assetType', 'creator', 'updater', 'actualizer', 'voider', 'utilizationEntries']);

        return view('fuel-orders.show', compact('fuelOrder'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FuelOrder $fuelOrder)
    {
        if (Auth::user()->role !== 'administrator') {
            abort(403, 'Unauthorized action. Only Administrators can edit fuel orders.');
        }

        $fuelOrder->load(['asset.assetType', 'creator', 'utilizationEntries']);

        return view('fuel-orders.edit', compact('fuelOrder'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FuelOrder $fuelOrder)
    {
        if (Auth::user()->role !== 'administrator') {
            abort(403, 'Unauthorized action. Only Administrators can update fuel orders.');
        }

        $validated = $request->validate([
            'say_quantity' => 'required|numeric|min:0',
            'actual_quantity' => 'required|numeric|min:0',
            'status' => 'required|in:PEND,DONE,VOID',
        ]);

        \Illuminate\Support\Facades\DB::transaction(function () use ($fuelOrder, $validated) {
            $fuelOrder->update([
                'say_quantity' => $validated['say_quantity'],
                'actual_quantity' => $validated['actual_quantity'],
                'status' => $validated['status'],
                'updated_by' => Auth::id(),
            ]);

            if ($validated['status'] === 'VOID') {
                $fuelOrder->utilizationEntries()->update(['fuel_order_id' => null]);
                $fuelOrder->update([
                    'void_by' => Auth::id(),
                    'void_at' => now(),
                ]);
            }
        });

        return redirect()->route('fuel-orders.index')->with('message', 'Fuel order updated successfully.');
    }

    /**
     * Show the form for actualizing the specified resource.
     */
    public function actualize(FuelOrder $fuelOrder)
    {
        if (!in_array(Auth::user()->role, ['data_logger', 'data logger', 'fuel_man', 'administrator'])) {
            abort(403, 'Unauthorized action.');
        }

        if ($fuelOrder->status !== 'PEND') {
            return redirect()->route('fuel-orders.index')->with('error', 'Only pending fuel orders can be actualized.');
        }

        $fuelOrder->load(['asset.assetType', 'creator', 'utilizationEntries']);

        return view('fuel-orders.actualize', compact('fuelOrder'));
    }

    /**
     * Store the actualization for the specified resource.
     */
    public function storeActualization(Request $request, FuelOrder $fuelOrder)
    {
        if (!in_array(Auth::user()->role, ['data_logger', 'data logger', 'fuel_man', 'administrator'])) {
            abort(403, 'Unauthorized action.');
        }

        if ($fuelOrder->status !== 'PEND') {
            return redirect()->route('fuel-orders.index')->with('error', 'Only pending fuel orders can be actualized.');
        }

        $validated = $request->validate([
            'actual_quantity' => 'required|numeric|min:0',
        ]);

        \Illuminate\Support\Facades\DB::transaction(function () use ($fuelOrder, $validated) {
            $status = $validated['actual_quantity'] > 0 ? 'DONE' : 'PEND';

            $fuelOrder->update([
                'actual_quantity' => $validated['actual_quantity'],
                'status' => $status,
                'actualized_by' => Auth::id(),
                'actualized_at' => now(),
                'updated_by' => Auth::id(),
            ]);
        });

        return redirect()->route('fuel-orders.index')->with('message', 'Fuel order actualized successfully.');
    }

    /**
     * Void the specified resource.
     */
    public function void(Request $request, FuelOrder $fuelOrder)
    {
        if (Auth::user()->role !== 'administrator') {
            abort(403, 'Unauthorized action. Only Administrators can void fuel orders.');
        }

        if ($fuelOrder->status === 'VOID') {
            return redirect()->back()->with('error', 'This fuel order is already voided.');
        }

        // Use a transaction to ensure both operations succeed
        \Illuminate\Support\Facades\DB::transaction(function () use ($fuelOrder) {
            // Remove fuel_order_id from all associated utilization entries
            $fuelOrder->utilizationEntries()->update(['fuel_order_id' => null]);

            // Update status to VOID
            $fuelOrder->update([
                'status' => 'VOID',
                'void_by' => Auth::id(),
                'void_at' => now(),
                'updated_by' => Auth::id(),
            ]);
        });

        return redirect()->route('fuel-orders.index')->with('message', 'Fuel order #'.str_pad($fuelOrder->id, 5, '0', STR_PAD_LEFT).' has been voided successfully.');
    }
}
