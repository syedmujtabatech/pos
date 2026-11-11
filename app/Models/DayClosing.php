<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DayClosing extends Model
{
    protected $fillable = [
        'closing_date',
        'location_id',
        'total_sales',
        'total_purchases',
        'total_receipts',
        'total_payments',
        'total_expenses',
        'cash_in_hand',
        'is_closed',
        'closed_by',
        'closed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'closing_date' => 'date',
            'closed_at' => 'datetime',
            'total_sales' => 'decimal:2',
            'total_purchases' => 'decimal:2',
            'total_receipts' => 'decimal:2',
            'total_payments' => 'decimal:2',
            'total_expenses' => 'decimal:2',
            'cash_in_hand' => 'decimal:2',
            'is_closed' => 'boolean',
        ];
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function closedBy()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }
}
