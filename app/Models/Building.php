<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Building extends Model
{
    protected $primaryKey = 'building_id';
    
    protected $fillable = [
        'name',
        'map_x',
        'map_y'
    ];

    protected $casts = [
        'map_x' => 'float',
        'map_y' => 'float'
    ];

    public function networks()
    {
        return $this->belongsToMany(
            \App\Models\Network::class,
            'building_networks',
            'building_id',
            'network_id'
        );
    }
}
