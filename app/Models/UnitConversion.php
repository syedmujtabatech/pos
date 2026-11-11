<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnitConversion extends Model
{
    protected $fillable = [
        'product_id',
        'from_unit_id',
        'to_unit_id',
        'conversion_factor',
    ];

    protected function casts(): array
    {
        return [
            'conversion_factor' => 'decimal:4',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function fromUnit()
    {
        return $this->belongsTo(ProductUnit::class, 'from_unit_id');
    }

    public function toUnit()
    {
        return $this->belongsTo(ProductUnit::class, 'to_unit_id');
    }
}
