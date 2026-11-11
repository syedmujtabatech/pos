<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductPriceHistory extends Model
{
    protected $table = 'product_price_history';

    protected $fillable = [
        'product_id',
        'old_cost',
        'new_cost',
        'old_retail_price',
        'new_retail_price',
        'old_wholesale_price',
        'new_wholesale_price',
        'changed_by',
        'reason',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
