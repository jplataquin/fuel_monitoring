<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetType;
use App\Models\ChargeableAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AssetController extends Controller
{
    public function index(): View
    {
        $assets = Asset::with('assetType')->get();

        return view('assets.index', compact('assets'));
    }

    public function create(): View
    {
        if (! in_array(Auth::user()->role, ['administrator', 'moderator'])) {
            abort(403);
        }
        $assetTypes = AssetType::all();

        return view('assets.create', compact('assetTypes'));
    }

    public function store(Request $request): RedirectResponse
    {
        if (! in_array(Auth::user()->role, ['administrator', 'moderator'])) {
            abort(403);
        }
        $validated = $request->validate([
            'fleet_no' => 'required|string|max:255|unique:assets',
            'asset_type_id' => 'required|exists:asset_types,id',
            'fuel_factor_km' => 'nullable|numeric',
            'fuel_factor_hr' => 'nullable|numeric',
            'plate_no' => 'nullable|string|max:255',
            'fuel_type' => 'required|in:Diesel,Gasoline',
            'tank_capacity' => 'required|numeric|min:0',
            'last_kilometer_reading' => 'required|numeric|min:0',
            'last_engine_hours' => 'required|numeric|min:0',
            'last_time' => 'nullable',
            'last_date' => 'nullable|date',
        ]);

        Asset::create($validated);

        return redirect()->route('assets.index')->with('status', 'Asset created successfully.');
    }

    public function show(Asset $asset): View
    {
        $chargeableAccounts = ChargeableAccount::where('status', 'Active')->orderBy('name', 'asc')->get();
        return view('assets.show', compact('asset', 'chargeableAccounts'));
    }

    public function edit(Asset $asset): View
    {
        if (! in_array(Auth::user()->role, ['administrator', 'moderator'])) {
            abort(403);
        }
        $assetTypes = AssetType::all();

        return view('assets.edit', compact('asset', 'assetTypes'));
    }

    public function update(Request $request, Asset $asset): RedirectResponse
    {
        if (! in_array(Auth::user()->role, ['administrator', 'moderator'])) {
            abort(403);
        }
        $validated = $request->validate([
            'fleet_no' => 'required|string|max:255|unique:assets,fleet_no,'.$asset->id,
            'asset_type_id' => 'required|exists:asset_types,id',
            'fuel_factor_km' => 'nullable|numeric',
            'fuel_factor_hr' => 'nullable|numeric',
            'plate_no' => 'nullable|string|max:255',
            'fuel_type' => 'required|in:Diesel,Gasoline',
            'tank_capacity' => 'required|numeric|min:0',
            'last_kilometer_reading' => 'required|numeric|min:0',
            'last_engine_hours' => 'required|numeric|min:0',
            'last_time' => 'nullable',
            'last_date' => 'nullable|date',
        ]);

        $asset->update($validated);

        return redirect()->route('assets.index')->with('status', 'Asset updated successfully.');
    }

    public function destroy(Asset $asset): RedirectResponse
    {
        if (! in_array(Auth::user()->role, ['administrator', 'moderator'])) {
            abort(403);
        }
        $asset->delete();

        return redirect()->route('assets.index')->with('status', 'Asset deleted successfully.');
    }
}
