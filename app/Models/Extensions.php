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
}
