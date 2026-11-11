<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockCountItem extends Model
{
    protected $fillable = [
        'stock_count_id',
        'product_id',
        'system_qty',
        'counted_qty',
        'variance_qty',
        'unit_cost',
    ];

    protected function casts(): array
    {
        return [
            'system_qty' => 'decimal:2',
            'counted_qty' => 'decimal:2',
            'variance_qty' => 'decimal:2',
            'unit_cost' => 'decimal:2',
        ];
    }

    public function stockCount()
    {
        return $this->belongsTo(StockCount::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
