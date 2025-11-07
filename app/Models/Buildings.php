<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Networks;

class Buildings extends Model
{
    protected $guarded = [];
    protected $primaryKey = 'building_id';
    

    public function networks()
    {
        return $this->belongsToMany(Networks::class, 'building_networks', 'building_id', 'network_id');
    }

   
}
