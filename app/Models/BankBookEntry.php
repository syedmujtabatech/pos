<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankBookEntry extends Model
{
    protected $fillable = [
        'entry_date',
        'type', // deposit, withdrawal
        'bank_name',
        'reference_id',
        'amount',
        'balance_after',
        'description',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
            'amount' => 'decimal:2',
            'balance_after' => 'decimal:2',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
