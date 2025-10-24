<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Devices extends Model
{
    public function building(){
        return $this->belongsTo(Buildings::class, 'building_id');
    }
    public function extensions(){
        return $this->hasMany(Extensions::class, 'device_id');
    }
}
