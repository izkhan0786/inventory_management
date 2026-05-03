<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\ProductVariant;
use Carbon\Carbon;
use Inertia\Inertia;

class MonitoringController extends Controller
{
    public function expiry()
    {
        $today = Carbon::today();
        $warningDate = Carbon::today()->addMonths(3);

        // Fetch from Batches
        $batchItems = Batch::with(['variant.product', 'warehouse'])
            ->whereNotNull('expiry_date')
            ->where('current_qty', '>', 0)
            ->get()
            ->map(function ($batch) use ($today, $warningDate) {
                $expiry = Carbon::parse($batch->expiry_date);
                return [
                    'id' => 'batch-' . $batch->id,
                    'product_name' => $batch->variant->product->name,
                    'variant_name' => $batch->variant->name,
                    'batch_number' => $batch->batch_number,
                    'expiry_date' => $batch->expiry_date,
                    'current_qty' => $batch->current_qty,
                    'days_left' => $today->diffInDays($expiry, false),
                    'status' => $this->calculateStatus($expiry, $warningDate)
                ];
            });

        // Fetch from Variants (for simple items without batches)
        $variantItems = ProductVariant::with('product')
            ->whereNotNull('expiry_date')
            ->where('stock_qty', '>', 0)
            ->get()
                ->map(function ($variant) use ($today, $warningDate) {
                    $expiry = Carbon::parse($variant->expiry_date);
                    return [
                        'id' => 'var-' . $variant->id,
                        'product_name' => $variant->product->name,
                        'variant_name' => $variant->name,
                        'batch_number' => 'N/A',
                        'expiry_date' => $variant->expiry_date,
                        'current_qty' => $variant->stock_qty,
                        'days_left' => $today->diffInDays($expiry, false),
                        'status' => $this->calculateStatus($expiry, $warningDate)
                    ];
                });

        $items = $batchItems->concat($variantItems)->sortBy('expiry_date')->values();

        return Inertia::render('Monitoring/Expiry', [
            'batches' => $items
        ]);
    }

    private function calculateStatus($expiry, $warningDate)
    {
        if ($expiry->isPast()) return 'Expired';
        if ($expiry->lessThanOrEqualTo($warningDate)) return 'Warning';
        return 'Safe';
    }

    public function alerts()
    {
        $lowStock = ProductVariant::with('product')
            ->whereColumn('stock_qty', '<=', 'min_stock_level')
            ->get()
            ->map(function ($variant) {
                return [
                    'id' => $variant->id,
                    'title' => 'Low Stock Alert',
                    'message' => "Stock for {$variant->product->name} ({$variant->sku}) is low: {$variant->stock_qty} units left.",
                    'type' => 'Stock',
                    'severity' => 'High',
                    'date' => $variant->updated_at,
                    'meta' => [
                        'current' => $variant->stock_qty,
                        'min' => $variant->min_stock_level,
                        'product_id' => $variant->product_id
                    ]
                ];
            });

        $expiringSoon = Batch::with('variant.product')
            ->where('current_qty', '>', 0)
            ->whereBetween('expiry_date', [Carbon::today(), Carbon::today()->addMonth()])
            ->get()
            ->map(function ($batch) {
                return [
                    'id' => 'exp-' . $batch->id,
                    'title' => 'Expiring Soon',
                    'message' => "Batch #{$batch->batch_number} of {$batch->variant->product->name} expires on {$batch->expiry_date}.",
                    'type' => 'Expiry',
                    'severity' => 'Critical',
                    'date' => $batch->updated_at,
                    'meta' => [
                        'expiry' => $batch->expiry_date,
                        'batch' => $batch->batch_number
                    ]
                ];
            });

        return Inertia::render('Monitoring/Alerts', [
            'alerts' => $lowStock->concat($expiringSoon)->sortByDesc('date')->values()
        ]);
    }
}
