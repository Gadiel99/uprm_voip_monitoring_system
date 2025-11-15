<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Network extends Model
{
    protected $primaryKey = 'network_id';
    
    protected $fillable = [
        'subnet',
        'offline_devices',
        'total_devices'
    ];

    protected $casts = [
        'offline_devices' => 'integer',
        'total_devices' => 'integer'
    ];

    public function buildings()
    {
        return $this->belongsToMany(
            Building::class,
            'building_networks',
            'network_id',
            'building_id'
        );
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
