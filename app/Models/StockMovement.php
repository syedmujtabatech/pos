<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class StockMovement extends Model
{
    protected $fillable = [
        'movement_date',
        'movement_type',
        'product_id',
        'location_id',
        'batch_id',
        'quantity',
        'unit_cost',
        'reference_type',
        'reference_id',
        'user_id',
        'balance_after',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'movement_date' => 'datetime',
            'quantity' => 'decimal:2',
            'unit_cost' => 'decimal:2',
            'balance_after' => 'decimal:2',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function batch()
    {
        return $this->belongsTo(InventoryBatch::class, 'batch_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Immutable Stock Ledger Entry (Golden Rule)
     */
    public static function recordMovement(
        int $productId,
        int $locationId,
        float $quantity, // positive for addition, negative for deduction
        string $movementType,
        ?string $referenceType = null,
        ?string $referenceId = null,
        float $unitCost = 0,
        ?int $batchId = null,
        ?string $remarks = null,
        ?int $userId = null
    ): self {
        return DB::transaction(function () use ($productId, $locationId, $quantity, $movementType, $referenceType, $referenceId, $unitCost, $batchId, $remarks, $userId) {
            $balance = StockBalance::lockForUpdate()->firstOrCreate(
                ['product_id' => $productId, 'location_id' => $locationId],
                ['on_hand' => 0, 'reserved' => 0, 'available' => 0]
            );

            $balance->on_hand += $quantity;
            $balance->available = max(0, $balance->on_hand - $balance->reserved);
            $balance->save();

            // If batch is provided, update batch quantity too
            if ($batchId) {
                $batch = InventoryBatch::lockForUpdate()->find($batchId);
                if ($batch) {
                    $batch->quantity += $quantity;
                    if ($batch->quantity <= 0) {
                        $batch->status = 'depleted';
                    }
                    $batch->save();
                }
            }

            return self::create([
                'movement_date' => now(),
                'movement_type' => $movementType,
                'product_id' => $productId,
                'location_id' => $locationId,
                'batch_id' => $batchId,
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'user_id' => $userId ?: auth()->id(),
                'balance_after' => $balance->on_hand,
                'remarks' => $remarks,
            ]);
        });
    }
}
