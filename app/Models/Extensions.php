<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Extensions extends Model
{
    protected $guarded = [];

    public function device()
    {
        return $this->belongsToMany(Devices::class, 'device_id');
    }

    public function building()
    {
        return $this->hasOneThrough(
            Buildings::class,
            Devices::class,
            'id', // Foreign key on Devices table...
            'id', // Foreign key on Buildings table...
            'device_id', // Local key on Extensions table...
            'building_id' // Local key on Devices table...
        );
    }
}
