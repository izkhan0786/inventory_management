<?php

namespace App\Services;

use App\Models\ProductVariant;
use App\Models\StockMovement;
use Carbon\Carbon;

class ForecastingService
{
    /**
     * Predict days until stockout for a variant based on historical consumption.
     */
    public function predictStockout(int $variantId, int $daysLookback = 30)
    {
        $variant = ProductVariant::findOrFail($variantId);
        
        // Get total consumption (STOCK OUT movements) in the period
        $totalOut = StockMovement::where('variant_id', $variantId)
            ->where('type', 'OUT')
            ->where('created_at', '>=', Carbon::now()->subDays($daysLookback))
            ->sum('quantity');

        if ($totalOut <= 0) {
            return [
                'daily_average' => 0,
                'days_remaining' => 'Infinity',
                'predicted_date' => null,
                'reorder_urgency' => 'Low'
            ];
        }

        $dailyAverage = abs($totalOut) / $daysLookback;
        $daysRemaining = floor($variant->stock_qty / $dailyAverage);
        
        return [
            'daily_average' => round($dailyAverage, 2),
            'days_remaining' => $daysRemaining,
            'predicted_date' => Carbon::now()->addDays($daysRemaining)->toDateString(),
            'reorder_suggestion' => $daysRemaining <= 7 ? 'Urgent Reorder Needed' : 'Monitor'
        ];
    }

    /**
     * Simple demand forecasting for 30/60/90 days based on linear trend.
     */
    public function getForecast(int $variantId)
    {
        return [
            '30_days' => $this->predictStockout($variantId, 30),
            '60_days' => $this->predictStockout($variantId, 60),
            '90_days' => $this->predictStockout($variantId, 90),
        ];
    }
}
