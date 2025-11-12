<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Networks;

class Devices extends Model
{
    protected $primaryKey = 'device_id';
    protected $fillable = ['mac_address', 'ip_address', 'network_id', 'status', 'is_critical', 'owner'];
    
    public function network()
    {

        return $this->belongsTo(Networks::class, 'network_id', 'network_id');
    
    }

    public function extensions()
    {

        return $this->belongsToMany(Extensions::class, 'device_extensions', 'device_id', 'extension_id')
        ->withTimestamps();
    }
}
