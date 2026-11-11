<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerSpecialPrice extends Model
{
    protected $fillable = ['customer_id', 'product_id', 'special_price'];

    protected function casts(): array
    {
        return [
            'special_price' => 'decimal:2',
        ];
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
