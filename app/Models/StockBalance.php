<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockBalance extends Model
{
    protected $fillable = [
        'product_id',
        'location_id',
        'on_hand',
        'reserved',
        'available',
    ];

    protected function casts(): array
    {
        return [
            'on_hand' => 'decimal:2',
            'reserved' => 'decimal:2',
            'available' => 'decimal:2',
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
