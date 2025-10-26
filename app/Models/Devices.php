<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Devices extends Model
{
    protected $guarded = [];
    protected $primaryKey = 'device_id';

    protected $casts = [
        'extension' => 'array', // Cast 'extension' JSON column to array
    ];
    public function building(){
        return $this->belongsTo(Buildings::class, 'building_id');
    }

    // public function extensions(){
    //     return $this->hasMany(Extensions::class, 'device_id');
    // }
}
