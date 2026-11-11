<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoodsReceipt extends Model
{
    protected $fillable = [
        'grn_number',
        'purchase_order_id',
        'supplier_id',
        'location_id',
        'received_date',
        'status',
        'freight_charge',
        'other_charges',
        'landed_cost_total',
        'notes',
        'received_by',
    ];

    protected function casts(): array
    {
        return [
            'received_date' => 'date',
            'freight_charge' => 'decimal:2',
            'other_charges' => 'decimal:2',
            'landed_cost_total' => 'decimal:2',
        ];
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function items()
    {
        return $this->hasMany(GoodsReceiptItem::class);
    }

    public function supplierInvoices()
    {
        return $this->hasMany(SupplierInvoice::class);
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
