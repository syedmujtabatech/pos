<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductUnit extends Model
{
    protected $fillable = ['name', 'short_name', 'is_base_unit'];

    protected function casts(): array
    {
        return [
            'is_base_unit' => 'boolean',
        ];
    }
}
