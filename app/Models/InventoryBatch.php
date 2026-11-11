<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryBatch extends Model
{
    protected $fillable = [
        'product_id',
        'location_id',
        'batch_number',
        'expiry_date',
        'cost_price',
        'quantity',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'expiry_date' => 'date',
            'cost_price' => 'decimal:2',
            'quantity' => 'decimal:2',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
