<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalRequest extends Model
{
    protected $fillable = [
        'request_type',
        'requested_by',
        'approver_id',
        'status',
        'reference_type',
        'reference_id',
        'data',
        'reason',
        'approver_notes',
        'responded_at',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'responded_at' => 'datetime',
        ];
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
