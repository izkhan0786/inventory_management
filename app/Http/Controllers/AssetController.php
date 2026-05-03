<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AssetController extends Controller
{
    public function index()
    {
        return Inertia::render('Assets/Index', [
            'assets' => Asset::with('user')->latest()->get(),
            'users' => \App\Models\User::all(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'asset_tag' => 'required|string|unique:assets,asset_tag',
            'serial_number' => 'nullable|string|max:100',
            'purchase_date' => 'nullable|date',
            'purchase_cost' => 'nullable|numeric|min:0',
            'status' => 'required|in:In Use,In Maintenance,Available,Retired,Broken',
            'location' => 'nullable|string|max:255',
            'user_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        Asset::create($validated);

        return to_route('assets.index')->with('success', 'Asset created successfully.');
    }

    public function update(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'asset_tag' => 'required|string|unique:assets,asset_tag,' . $asset->id,
            'serial_number' => 'nullable|string|max:100',
            'purchase_date' => 'nullable|date',
            'purchase_cost' => 'nullable|numeric|min:0',
            'status' => 'required|in:In Use,In Maintenance,Available,Retired,Broken',
            'location' => 'nullable|string|max:255',
            'user_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        $asset->update($validated);

        return to_route('assets.index')->with('success', 'Asset updated successfully.');
    }

    public function destroy(Asset $asset)
    {
        $asset->delete();
        return to_route('assets.index')->with('success', 'Asset deleted successfully.');
    }

}
