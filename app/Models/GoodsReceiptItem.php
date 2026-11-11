<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoodsReceiptItem extends Model
{
    protected $fillable = [
        'goods_receipt_id',
        'po_item_id',
        'product_id',
        'unit_id',
        'received_qty',
        'base_qty',
        'unit_cost',
        'allocated_landed_cost',
        'batch_number',
        'expiry_date',
        'bin_id',
        'total_cost',
    ];

    protected function casts(): array
    {
        return [
            'received_qty' => 'decimal:2',
            'base_qty' => 'decimal:2',
            'unit_cost' => 'decimal:2',
            'allocated_landed_cost' => 'decimal:2',
            'total_cost' => 'decimal:2',
            'expiry_date' => 'date',
        ];
    }

    public function goodsReceipt()
    {
        return $this->belongsTo(GoodsReceipt::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function unit()
    {
        return $this->belongsTo(ProductUnit::class);
    }

    public function bin()
    {
        return $this->belongsTo(Bin::class);
    }
}
