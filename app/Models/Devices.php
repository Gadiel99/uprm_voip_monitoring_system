<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Networks;

class Devices extends Model
{
    protected $guarded = [];
    
    public function network()
    {

        return $this->belongsTo(Networks::class, 'network_id', 'network_id');
    
    }

    public function extension()
    {

        return $this->belongsToMany(Extensions::class, 'device_extensions', 'device_id', 'extension_id')
        ->withTimestamps();
    }
}
