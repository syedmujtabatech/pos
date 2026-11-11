<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number',
        'invoice_type',
        'customer_id',
        'salesman_id',
        'location_id',
        'pos_shift_id',
        'sales_order_id',
        'invoice_date',
        'payment_type',
        'subtotal',
        'product_discount',
        'invoice_discount',
        'tax',
        'delivery_charges',
        'grand_total',
        'paid_amount',
        'due_amount',
        'cogs_total',
        'profit_total',
        'status',
        'void_reason',
        'void_approved_by',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'subtotal' => 'decimal:2',
            'product_discount' => 'decimal:2',
            'invoice_discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'delivery_charges' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'due_amount' => 'decimal:2',
            'cogs_total' => 'decimal:2',
            'profit_total' => 'decimal:2',
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

    public function posShift()
    {
        return $this->belongsTo(PosShift::class);
    }

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments()
    {
        return $this->hasMany(InvoicePayment::class);
    }

    public function salesReturns()
    {
        return $this->hasMany(SalesReturn::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function voidApprovedBy()
    {
        return $this->belongsTo(User::class, 'void_approved_by');
    }
}
