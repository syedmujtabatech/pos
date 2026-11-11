<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cheque extends Model
{
    protected $fillable = [
        'type',
        'cheque_number',
        'bank_name',
        'issue_date',
        'due_date',
        'amount',
        'party_type',
        'party_id',
        'status',
        'bounced_reason',
        'bounced_at',
    ];

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'due_date' => 'date',
            'bounced_at' => 'datetime',
            'amount' => 'decimal:2',
        ];
    }

    public function getPartyAttribute()
    {
        if ($this->party_type === 'customer') {
            return Customer::find($this->party_id);
        }
        return Supplier::find($this->party_id);
    }
}
