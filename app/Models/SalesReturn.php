<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesReturn extends Model
{
    protected $fillable = [
        'return_number',
        'invoice_id',
        'customer_id',
        'location_id',
        'return_date',
        'refund_amount',
        'return_status',
        'reason',
        'created_by',
        'approved_by',
    ];

    protected function casts(): array
    {
        return [
            'return_date' => 'date',
            'refund_amount' => 'decimal:2',
        ];
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function items()
    {
        return $this->hasMany(SalesReturnItem::class);
    }

    public function creditNote()
    {
        return $this->hasOne(CreditNote::class);
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
