<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Networks extends Model
{
    protected $primaryKey = 'network_id';
    protected $guarded = [];

    public function buildings()
    {
        
        return $this->belongsTo(Buildings::class, 'building_id', 'building_id');
    
    }

    public function devices()
    {
        
        return $this->hasMany(Devices::class, 'network_id', 'network_id');
    
    }

    public function updateDeviceCounts()
    {
        
        $this->total_devices = $this->devices()->count();
        $this->offline_devices = $this->devices()->where('status', 'offline')->count();
        $this->save();
    
    }
}
