<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Networks;

/**
 * Definition of the Buildings model.
 */
class Buildings extends Model
{
    // Table associated with the model
    protected $table = 'buildings';

    // Mass assignable attributes
    protected $guarded = [];

    // Primary key for the buildings table
    protected $primaryKey = 'building_id';
    
    /**
     * Function that defines the relationship to the Networks model.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<Networks, Buildings, \Illuminate\Database\Eloquent\Relations\Pivot>
     */
    public function networks()
    {
        // Many-to-Many relationship with Networks.
        return $this->belongsToMany(Networks::class, 'building_networks', 'building_id', 'network_id');
    }

   
}
