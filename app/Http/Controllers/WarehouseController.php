<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WarehouseController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Locations/Index', [
            'locations' => Warehouse::latest()->paginate(10),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'is_default' => 'boolean',
        ]);

        if ($validated['is_default']) {
            Warehouse::where('is_default', true)->update(['is_default' => false]);
        }

        Warehouse::create($validated);

        return to_route('locations.index')->with('success', 'Location created successfully.');
    }

    public function update(Request $request, Warehouse $location)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'is_default' => 'boolean',
        ]);

        if ($validated['is_default']) {
            Warehouse::where('id', '!=', $location->id)->update(['is_default' => false]);
        }

        $location->update($validated);

        return to_route('locations.index')->with('success', 'Location updated successfully.');
    }

    public function destroy(Warehouse $location)
    {
        if ($location->is_default) {
            return back()->with('error', 'Cannot delete the default location.');
        }

        $location->delete();

        return to_route('locations.index')->with('success', 'Location deleted successfully.');
    }
}
