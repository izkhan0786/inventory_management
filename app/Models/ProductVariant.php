<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'sku', 'name', 'cost_price', 'selling_price', 
        'stock_qty', 'min_stock_level', 'barcode', 'attributes', 'expiry_date'
    ];

    protected $casts = [
        'attributes' => 'array',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'variant_id');
    }

    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class, 'variant_id');
    }

    /**
     * Get the expiry status classified by days remaining.
     */
    public function getExpiryStatusAttribute()
    {
        $batch = $this->batches()->where('current_qty', '>', 0)->orderBy('expiry_date', 'asc')->first();
        
        if (!$batch || !$batch->expiry_date) return 'Safe';

        $days = now()->diffInDays($batch->expiry_date, false);

        if ($days < 0) return 'Expired';
        if ($days <= 7) return 'Critical';
        if ($days <= 30) return 'Warning';
        if ($days <= 90) return 'Approaching';
        
        return 'Safe';
    }
}
