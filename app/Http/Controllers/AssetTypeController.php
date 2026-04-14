<?php

namespace App\Http\Controllers;

use App\Models\AssetType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssetTypeController extends Controller
{
    public function index(): View
    {
        $assetTypes = AssetType::all();

        return view('asset-types.index', compact('assetTypes'));
    }

    public function create(): View
    {
        return view('asset-types.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:asset_types',
        ]);

        AssetType::create($validated);

        return redirect()->route('asset-types.index')->with('status', 'Asset type created successfully.');
    }

    public function edit(AssetType $assetType): View
    {
        return view('asset-types.edit', compact('assetType'));
    }

    public function update(Request $request, AssetType $assetType): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:asset_types,name,'.$assetType->id,
        ]);

        $assetType->update($validated);

        return redirect()->route('asset-types.index')->with('status', 'Asset type updated successfully.');
    }

    public function destroy(AssetType $assetType): RedirectResponse
    {
        $assetType->delete();

        return redirect()->route('asset-types.index')->with('status', 'Asset type deleted successfully.');
    }
}
