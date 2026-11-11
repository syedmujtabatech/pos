<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerReceipt extends Model
{
    protected $fillable = [
        'receipt_number',
        'customer_id',
        'salesman_id',
        'receipt_date',
        'payment_method',
        'amount',
        'reference_no',
        'cheque_no',
        'cheque_date',
        'status',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'receipt_date' => 'date',
            'cheque_date' => 'date',
            'amount' => 'decimal:2',
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

    public function allocations()
    {
        return $this->hasMany(CustomerReceiptAllocation::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
