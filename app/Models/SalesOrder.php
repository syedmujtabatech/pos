<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesOrder extends Model
{
    protected $fillable = [
        'order_number',
        'customer_id',
        'salesman_id',
        'location_id',
        'order_date',
        'status',
        'subtotal',
        'discount',
        'tax',
        'delivery_charges',
        'total',
        'credit_override_approved',
        'notes',
        'created_by',
        'approved_by',
    ];

    protected function casts(): array
    {
        return [
            'order_date' => 'date',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'delivery_charges' => 'decimal:2',
            'total' => 'decimal:2',
            'credit_override_approved' => 'boolean',
        ];
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function salesman()
    {
        return $this->belongsTo(User::class, 'salesman_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function items()
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    public function challans()
    {
        return $this->hasMany(DeliveryChallan::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
