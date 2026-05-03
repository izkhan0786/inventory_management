<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\ProductVariant;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BatchController extends Controller
{
    public function index()
    {
        return Inertia::render('Lots/Index', [
            'batches' => Batch::with(['variant.product', 'warehouse'])->latest()->get(),
            'variants' => ProductVariant::with('product')->orderBy('sku')->get(),
            'warehouses' => Warehouse::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'batch_number' => 'required|string|max:255|unique:batches,batch_number',
            'variant_id' => 'required|exists:product_variants,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'manufacturing_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'initial_qty' => 'required|integer|min:0',
            'current_qty' => 'required|integer|min:0',
        ]);

        Batch::create($validated);

        return to_route('lots.index')->with('success', 'Lot created successfully.');
    }

    public function update(Request $request, Batch $batch)
    {
        $validated = $request->validate([
            'batch_number' => 'required|string|max:255|unique:batches,batch_number,' . $batch->id,
            'warehouse_id' => 'required|exists:warehouses,id',
            'manufacturing_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'current_qty' => 'required|integer|min:0',
        ]);

        $batch->update($validated);

        return to_route('lots.index')->with('success', 'Lot updated successfully.');
    }

    public function destroy(Batch $batch)
    {
        $batch->delete();

        return to_route('lots.index')->with('success', 'Lot deleted successfully.');
    }
}
