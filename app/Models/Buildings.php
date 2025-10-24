<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Buildings extends Model
{
    protected $guarded = [];

    public function devices()
    {
        return $this->hasMany(Devices::class, 'building_id');
    }

    public function extensions()
    {
        return $this->hasManyThrough(Extensions::class, Devices::class, 'building_id', 'device_id');
    }
}
