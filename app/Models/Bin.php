<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bin extends Model
{
    protected $fillable = ['rack_id', 'name', 'code', 'is_damaged_bin'];

    protected function casts(): array
    {
        return [
            'is_damaged_bin' => 'boolean',
        ];
    }

    public function rack()
    {
        return $this->belongsTo(Rack::class);
    }
}
