<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $variants = ProductVariant::query()
            ->with([
                'product.category:id,name,color_hex',
                'batches' => fn ($query) => $query
                    ->select('id', 'variant_id', 'expiry_date', 'current_qty')
                    ->where('current_qty', '>', 0)
                    ->orderBy('expiry_date'),
            ])
            ->get(['id', 'product_id', 'sku', 'name', 'stock_qty', 'min_stock_level', 'selling_price', 'barcode']);

        $totalItems = (int) $variants->sum('stock_qty');
        $lowStockCount = $variants->filter(fn ($variant) => $variant->stock_qty > 0 && $variant->stock_qty < $variant->min_stock_level)->count();
        $outOfStockCount = $variants->where('stock_qty', '<=', 0)->count();
        $totalValue = (float) $variants->sum(fn ($variant) => $variant->stock_qty * $variant->selling_price);

        $health = $variants
            ->map(fn ($variant) => $this->resolveExpiryStatus($variant))
            ->countBy()
            ->all();

        $pendingOrders = Order::query()
            ->whereIn('status', ['Pending', 'Processing'])
            ->count();

        $activeLocations = DB::table('warehouses')->count();

        $expiringBatches = Batch::query()
            ->where('current_qty', '>', 0)
            ->whereBetween('expiry_date', [now()->toDateString(), now()->addDays(30)->toDateString()])
            ->count();

        $recentMovements = StockMovement::with(['variant.product.category', 'warehouse'])
            ->latest()
            ->take(5)
            ->get();

        $recentOrders = Order::query()
            ->latest()
            ->take(5)
            ->get(['id', 'order_number', 'status', 'total_amount', 'created_at']);

        $categoryDistribution = $variants
            ->groupBy(fn ($variant) => $variant->product?->category?->name ?: 'Uncategorized')
            ->map(function (Collection $group, string $name) use ($totalItems) {
                $quantity = (int) $group->sum('stock_qty');
                $color = $group->first()?->product?->category?->color_hex ?: '#22c55e';

                return [
                    'name' => $name,
                    'quantity' => $quantity,
                    'percentage' => $totalItems > 0 ? round(($quantity / $totalItems) * 100, 1) : 0,
                    'color' => $color,
                ];
            })
            ->sortByDesc('quantity')
            ->take(5)
            ->values();

        $movementTrend = $this->buildMovementTrend();

        $warehouseDistribution = DB::table('batches')
            ->join('warehouses', 'warehouses.id', '=', 'batches.warehouse_id')
            ->select('warehouses.name', 'warehouses.location', DB::raw('SUM(batches.current_qty) as quantity'))
            ->groupBy('warehouses.id', 'warehouses.name', 'warehouses.location')
            ->orderByDesc('quantity')
            ->get()
            ->map(function ($warehouse) use ($totalItems) {
                $quantity = (int) $warehouse->quantity;

                return [
                    'name' => $warehouse->name,
                    'location' => $warehouse->location,
                    'quantity' => $quantity,
                    'percentage' => $totalItems > 0 ? round(($quantity / $totalItems) * 100, 1) : 0,
                ];
            });

        $reorderSuggestions = $variants
            ->filter(fn ($variant) => $variant->stock_qty < $variant->min_stock_level)
            ->sortBy(fn ($variant) => $variant->stock_qty - $variant->min_stock_level)
            ->take(5)
            ->map(function ($variant) {
                $recommended = max(($variant->min_stock_level * 2) - $variant->stock_qty, $variant->min_stock_level);

                return [
                    'id' => $variant->id,
                    'name' => $variant->product?->name ?: 'Unknown product',
                    'sku' => $variant->sku,
                    'stock_qty' => (int) $variant->stock_qty,
                    'min_stock_level' => (int) $variant->min_stock_level,
                    'recommended_qty' => (int) $recommended,
                    'barcode' => $variant->barcode,
                ];
            })
            ->values();

        return Inertia::render('Dashboard', [
            'stats' => [
                'totalItems' => $totalItems,
                'lowStock' => $lowStockCount,
                'outOfStock' => $outOfStockCount,
                'totalValue' => round($totalValue, 2),
                'pendingOrders' => $pendingOrders,
                'activeLocations' => $activeLocations,
                'expiringBatches' => $expiringBatches,
                'categoriesCount' => DB::table('categories')->count(),
            ],
            'health' => [
                'safe' => $health['Safe'] ?? 0,
                'approaching' => $health['Approaching'] ?? 0,
                'warning' => $health['Warning'] ?? 0,
                'critical' => $health['Critical'] ?? 0,
                'expired' => $health['Expired'] ?? 0,
            ],
            'categoryDistribution' => $categoryDistribution,
            'movementTrend' => $movementTrend,
            'warehouseDistribution' => $warehouseDistribution,
            'reorderSuggestions' => $reorderSuggestions,
            'recentMovements' => $recentMovements,
            'recentOrders' => $recentOrders,
        ]);
    }

    protected function buildMovementTrend(): array
    {
        $start = now()->subDays(6)->startOfDay();
        $end = now()->endOfDay();

        $rawTrend = StockMovement::query()
            ->selectRaw('DATE(created_at) as day')
            ->selectRaw("SUM(CASE WHEN type = 'IN' THEN quantity ELSE 0 END) as inbound")
            ->selectRaw("SUM(CASE WHEN type = 'OUT' THEN quantity ELSE 0 END) as outbound")
            ->whereBetween('created_at', [$start, $end])
            ->groupByRaw('DATE(created_at)')
            ->get()
            ->keyBy('day');

        return collect(CarbonPeriod::create($start, '1 day', $end))
            ->map(function (Carbon $date) use ($rawTrend) {
                $day = $date->toDateString();
                $metrics = $rawTrend->get($day);

                return [
                    'label' => $date->format('D'),
                    'date' => $day,
                    'inbound' => (int) ($metrics->inbound ?? 0),
                    'outbound' => (int) ($metrics->outbound ?? 0),
                ];
            })
            ->all();
    }

    protected function resolveExpiryStatus(ProductVariant $variant): string
    {
        $batch = $variant->batches->first();

        if (!$batch || !$batch->expiry_date) {
            return 'Safe';
        }

        $days = now()->diffInDays($batch->expiry_date, false);

        return match (true) {
            $days < 0 => 'Expired',
            $days <= 7 => 'Critical',
            $days <= 30 => 'Warning',
            $days <= 90 => 'Approaching',
            default => 'Safe',
        };
    }
}
