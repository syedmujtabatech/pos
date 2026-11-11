<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesReturnItem extends Model
{
    protected $fillable = [
        'sales_return_id',
        'product_id',
        'batch_id',
        'unit_id',
        'quantity',
        'base_quantity',
        'unit_price',
        'condition',
        'is_restocked',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'base_quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'is_restocked' => 'boolean',
        ];
    }

    public function salesReturn()
    {
        return $this->belongsTo(SalesReturn::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function batch()
    {
        return $this->belongsTo(InventoryBatch::class);
    }

    public function unit()
    {
        return $this->belongsTo(ProductUnit::class);
    }
}
