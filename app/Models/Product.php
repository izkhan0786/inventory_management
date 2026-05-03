<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'sku', 'category_id', 'description', 'has_variants', 'brand', 'image_path', 'unit'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    /**
     * Get the total inventory value for this product.
     */
    public function getValuationAttribute()
    {
        return $this->variants->sum(function ($variant) {
            return $variant->cost_price * $variant->stock_qty;
        });
    }
}
