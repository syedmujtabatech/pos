<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'shop_name',
        'phone',
        'email',
        'address',
        'area_id',
        'assigned_salesman_id',
        'customer_type',
        'opening_balance',
        'current_balance',
        'credit_limit',
        'payment_terms_days',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'opening_balance' => 'decimal:2',
            'current_balance' => 'decimal:2',
            'credit_limit' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function assignedSalesman()
    {
        return $this->belongsTo(User::class, 'assigned_salesman_id');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function receipts()
    {
        return $this->hasMany(CustomerReceipt::class);
    }

    public function specialPrices()
    {
        return $this->hasMany(CustomerSpecialPrice::class);
    }

    public function creditNotes()
    {
        return $this->hasMany(CreditNote::class);
    }

    public function hasOverdueInvoices(int $days = 60): bool
    {
        return $this->invoices()
            ->where('status', 'posted')
            ->where('due_amount', '>', 0)
            ->where('invoice_date', '<', now()->subDays($days)->toDateString())
            ->exists();
    }

    public function isCreditLimitExceeded(float $additionalAmount = 0): bool
    {
        if ($this->credit_limit <= 0) return false;
        return ($this->current_balance + $additionalAmount) > $this->credit_limit;
    }
}
