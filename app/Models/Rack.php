<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rack extends Model
{
    protected $fillable = ['location_id', 'name', 'code'];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function bins()
    {
        return $this->hasMany(Bin::class);
    }
}
