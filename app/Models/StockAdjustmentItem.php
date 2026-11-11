<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockAdjustmentItem extends Model
{
    protected $fillable = [
        'stock_adjustment_id',
        'product_id',
        'batch_id',
        'current_qty',
        'adjusted_qty',
        'diff_qty',
        'unit_cost',
    ];

    protected function casts(): array
    {
        return [
            'current_qty' => 'decimal:2',
            'adjusted_qty' => 'decimal:2',
            'diff_qty' => 'decimal:2',
            'unit_cost' => 'decimal:2',
        ];
    }

    public function adjustment()
    {
        return $this->belongsTo(StockAdjustment::class, 'stock_adjustment_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function batch()
    {
        return $this->belongsTo(InventoryBatch::class);
    }
}
