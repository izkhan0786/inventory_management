<?php

namespace App\Http\Controllers;

use App\Models\ProductVariant;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class InventoryController extends Controller
{
    /**
     * Display a listing of stock movements.
     */
    public function index()
    {
        $movements = StockMovement::with(['variant.product.category', 'warehouse', 'user'])->latest()->get();
        $variants = ProductVariant::with(['product.category'])->get();

        return Inertia::render('Inventory/Index', [
            'movements' => $movements,
            'variants' => $variants,
            'warehouses' => \App\Models\Warehouse::all(),
            'categories' => \App\Models\Category::all(),
            'summary' => [
                'movementCount' => $movements->count(),
                'lowStockVariants' => $variants->filter(fn ($variant) => $variant->stock_qty > 0 && $variant->stock_qty < $variant->min_stock_level)->count(),
                'outboundToday' => $movements->where('type', 'OUT')->where('created_at', '>=', now()->startOfDay())->sum('quantity'),
                'inboundToday' => $movements->where('type', 'IN')->where('created_at', '>=', now()->startOfDay())->sum('quantity'),
            ],
        ]);
    }

    /**
     * Record a stock movement (In/Out/Transfer).
     */
    public function recordMovement(Request $request)
    {
        $request->validate([
            'variant_id' => 'required|exists:product_variants,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'type' => 'required|in:IN,OUT,ADJUSTMENT,TRANSFER',
            'quantity' => 'required|integer',
            'reference_no' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request) {
            $variant = ProductVariant::lockForUpdate()->findOrFail($request->variant_id);
            $newQty = $this->calculateMovementAdjustedQuantity($variant->stock_qty, $request->type, $request->quantity);

            StockMovement::create([
                'variant_id' => $request->variant_id,
                'warehouse_id' => $request->warehouse_id,
                'type' => $request->type,
                'quantity' => $request->quantity,
                'balance_after' => $newQty,
                'reference_no' => $request->reference_no,
                'user_id' => auth()->id(),
                'notes' => $request->notes,
            ]);

            $variant->update(['stock_qty' => $newQty]);

            return redirect()->back()->with('success', 'Stock movement recorded.');
        });
    }

    public function updateMovement(Request $request, StockMovement $movement)
    {
        $request->validate([
            'variant_id' => 'required|exists:product_variants,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'type' => 'required|in:IN,OUT,ADJUSTMENT,TRANSFER',
            'quantity' => 'required|integer',
            'reference_no' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request, $movement) {
            $originalVariant = $movement->variant;

            $movement->update([
                'variant_id' => $request->variant_id,
                'warehouse_id' => $request->warehouse_id,
                'type' => $request->type,
                'quantity' => $request->quantity,
                'reference_no' => $request->reference_no,
                'notes' => $request->notes,
            ]);

            $this->recalculateVariantStock($originalVariant);
            $this->recalculateVariantStock($movement->variant);

            return redirect()->back()->with('success', 'Stock movement updated.');
        });
    }

    public function destroyMovement(StockMovement $movement)
    {
        return DB::transaction(function () use ($movement) {
            $variant = $movement->variant;
            $movement->delete();
            $this->recalculateVariantStock($variant);

            return redirect()->back()->with('success', 'Stock movement deleted.');
        });
    }

    protected function calculateMovementAdjustedQuantity(int $currentQty, string $type, int $quantity): int
    {
        return $currentQty + $this->calculateMovementDelta($type, $quantity);
    }

    protected function calculateMovementDelta(string $type, int $quantity): int
    {
        return match ($type) {
            'IN' => $quantity,
            'OUT' => -abs($quantity),
            'ADJUSTMENT' => $quantity,
            'TRANSFER' => 0,
            default => 0,
        };
    }

    protected function recalculateVariantStock(ProductVariant $variant): void
    {
        $balance = 0;

        $movements = $variant->movements()
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        foreach ($movements as $record) {
            $balance += $this->calculateMovementDelta($record->type, $record->quantity);
            $record->update(['balance_after' => $balance]);
        }

        $variant->update(['stock_qty' => $balance]);
    }

    /**
     * Display a listing of stock take sessions.
     */
    public function stockTake()
    {
        return Inertia::render('Inventory/StockTake');
    }

    /**
     * Display stock level report.
     */
    public function stockReport()
    {
        return Inertia::render('Reports/StockLevels');
    }

    /**
     * Display barcode scanner interface.
     */
    public function scanner()
    {
        return Inertia::render('Inventory/Scanner', [
            'warehouses' => \App\Models\Warehouse::all(),
        ]);
    }
}
