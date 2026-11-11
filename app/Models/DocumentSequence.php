<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DocumentSequence extends Model
{
    protected $fillable = ['prefix', 'next_number'];

    public static function getNextNumber(string $prefix, int $padLength = 5): string
    {
        return DB::transaction(function () use ($prefix, $padLength) {
            $seq = self::lockForUpdate()->firstOrCreate(
                ['prefix' => $prefix],
                ['next_number' => 1]
            );

            $num = $seq->next_number;
            $seq->increment('next_number');

            return $prefix . '-' . str_pad($num, $padLength, '0', STR_PAD_LEFT);
        });
    }
}
