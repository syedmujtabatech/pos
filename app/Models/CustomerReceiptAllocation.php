<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerReceiptAllocation extends Model
{
    protected $fillable = [
        'customer_receipt_id',
        'invoice_id',
        'allocated_amount',
    ];

    protected function casts(): array
    {
        return [
            'allocated_amount' => 'decimal:2',
        ];
    }

    public function receipt()
    {
        return $this->belongsTo(CustomerReceipt::class, 'customer_receipt_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
