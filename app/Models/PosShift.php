<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosShift extends Model
{
    protected $fillable = [
        'cashier_id',
        'location_id',
        'opened_at',
        'closed_at',
        'opening_cash',
        'closing_cash_system',
        'closing_cash_counted',
        'difference',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
            'opening_cash' => 'decimal:2',
            'closing_cash_system' => 'decimal:2',
            'closing_cash_counted' => 'decimal:2',
            'difference' => 'decimal:2',
        ];
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
