<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierPaymentAllocation extends Model
{
    protected $fillable = [
        'supplier_payment_id',
        'supplier_invoice_id',
        'allocated_amount',
    ];

    protected function casts(): array
    {
        return [
            'allocated_amount' => 'decimal:2',
        ];
    }

    public function payment()
    {
        return $this->belongsTo(SupplierPayment::class, 'supplier_payment_id');
    }

    public function invoice()
    {
        return $this->belongsTo(SupplierInvoice::class, 'supplier_invoice_id');
    }
}
