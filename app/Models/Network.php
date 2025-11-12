<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Network extends Model
{
    protected $primaryKey = 'network_id';
    
    protected $fillable = [
        'subnet'
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
}
