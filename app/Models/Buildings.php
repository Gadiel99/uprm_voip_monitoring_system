<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Buildings extends Model
{
    protected $guarded = [];
    protected $primaryKey = 'building_id';
    

    public function devices()
    {
        return $this->hasMany(Devices::class, 'building_id', 'building_id');
    }

    public function updateDeviceCounts()
    {
        $this->total_devices = $this->devices()->count();
        $this->offline_devices = $this->devices()->where('status', 'offline')->count();
        $this->save();
    }

   
}
