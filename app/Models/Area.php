<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    protected $fillable = ['name', 'city', 'route_code', 'assigned_salesman_id'];

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function assignedSalesman()
    {
        return $this->belongsTo(User::class, 'assigned_salesman_id');
    }
}
