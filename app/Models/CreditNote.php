<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditNote extends Model
{
    protected $fillable = [
        'credit_note_number',
        'customer_id',
        'sales_return_id',
        'amount',
        'status',
        'date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function salesReturn()
    {
        return $this->belongsTo(SalesReturn::class);
    }
}
