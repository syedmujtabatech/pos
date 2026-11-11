<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockCount extends Model
{
    protected $fillable = [
        'count_number',
        'location_id',
        'count_date',
        'status',
        'conducted_by',
        'approved_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'count_date' => 'date',
        ];
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function items()
    {
        return $this->hasMany(StockCountItem::class);
    }

    public function conductedBy()
    {
        return $this->belongsTo(User::class, 'conducted_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
