<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Networks extends Model
{
    // Primary key for the networks table
    protected $primaryKey = 'network_id';

    // Mass assignable attributes
    protected $guarded = [];

    /**
     * This function defines the relationship to the Buildings model.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Buildings, Networks>
     */
    public function buildings()
    {
        // Many-to-One relationship with Buildings.
        return $this->belongsTo(Buildings::class, 'building_id', 'building_id');
    
    }

    /**
     * This function defines the relationship to the Devices model.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<Devices, Networks>
     */
    public function devices()
    {
        // One-to-Many relationship with Devices.
        return $this->hasMany(Devices::class, 'network_id', 'network_id');
    
    }

 
    // public function updateDeviceCounts()
    // {
        
    //     $this->total_devices = $this->devices()->count();
    //     $this->offline_devices = $this->devices()->where('status', 'offline')->count();
    //     $this->save();
    
    // }
}
