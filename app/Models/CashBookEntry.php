<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashBookEntry extends Model
{
    protected $fillable = [
        'entry_date',
        'type', // inflow, outflow
        'source',
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

    public static function record(string $type, float $amount, string $source, ?string $referenceId = null, ?string $description = null): self
    {
        $lastEntry = self::latest('id')->first();
        $prevBalance = $lastEntry ? $lastEntry->balance_after : 0;
        $newBalance = $type === 'inflow' ? ($prevBalance + $amount) : ($prevBalance - $amount);

        return self::create([
            'entry_date' => now()->toDateString(),
            'type' => $type,
            'source' => $source,
            'reference_id' => $referenceId,
            'amount' => $amount,
            'balance_after' => $newBalance,
            'description' => $description,
            'user_id' => auth()->id(),
        ]);
    }
}
