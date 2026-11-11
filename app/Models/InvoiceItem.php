<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id',
        'product_id',
        'batch_id',
        'unit_id',
        'quantity',
        'base_quantity',
        'unit_price',
        'unit_cost',
        'discount',
        'tax',
        'total_price',
        'total_cost',
        'profit',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'base_quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'unit_cost' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'total_price' => 'decimal:2',
            'total_cost' => 'decimal:2',
            'profit' => 'decimal:2',
        ];
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
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
