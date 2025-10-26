<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Devices extends Model
{
    protected $guarded = [];
    protected $primaryKey = 'device_id';

    protected $casts = [
        'extensions' => 'array', // Cast 'extensions' JSON column to array
    ];
    public function building(){
        return $this->belongsTo(Buildings::class, 'building_id', 'building_id');
    }

}
