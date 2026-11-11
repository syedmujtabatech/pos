<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $fillable = ['name', 'code', 'type', 'address', 'phone', 'is_active'];

    public function racks()
    {
        return $this->hasMany(Rack::class);
    }

    public function stockBalances()
    {
        return $this->hasMany(StockBalance::class);
    }

    public function batches()
    {
        return $this->hasMany(InventoryBatch::class);
    }
}
