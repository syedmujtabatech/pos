<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'name_ur',
        'sku',
        'barcode',
        'category_id',
        'brand_id',
        'manufacturer_id',
        'base_unit_id',
        'purchase_cost',
        'retail_price',
        'wholesale_price',
        'min_margin_percent',
        'tax_percent',
        'reorder_level',
        'max_stock',
        'expiry_tracking',
        'is_active',
        'image',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'purchase_cost' => 'decimal:2',
            'retail_price' => 'decimal:2',
            'wholesale_price' => 'decimal:2',
            'min_margin_percent' => 'decimal:2',
            'tax_percent' => 'decimal:2',
            'expiry_tracking' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function manufacturer()
    {
        return $this->belongsTo(Manufacturer::class);
    }

    public function baseUnit()
    {
        return $this->belongsTo(ProductUnit::class, 'base_unit_id');
    }

    public function conversions()
    {
        return $this->hasMany(UnitConversion::class);
    }

    public function specialPrices()
    {
        return $this->hasMany(CustomerSpecialPrice::class);
    }

    public function batches()
    {
        return $this->hasMany(InventoryBatch::class);
    }

    public function stockBalances()
    {
        return $this->hasMany(StockBalance::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function priceHistory()
    {
        return $this->hasMany(ProductPriceHistory::class);
    }

    public function getTotalStockAttribute()
    {
        return $this->stockBalances()->sum('on_hand');
    }

    public function getAvailableStockAttribute()
    {
        return $this->stockBalances()->sum('available');
    }
}
