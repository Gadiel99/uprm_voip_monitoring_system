<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Networks;

/**
 * Definition of the Devices model.
 */
class Devices extends Model
{
    // Table associated with the model
    protected $table = 'devices';

    // Primary key for the devices table
    protected $primaryKey = 'device_id';

    // Mass assignable attributes
    protected $fillable = ['ip_address', 'mac_address', 'network_id', 'status', 'is_critical'];
    
    /**
     * Function that defines the relationship to the Networks model.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Networks, Devices>
     */
    public function network()
    {

        return $this->belongsTo(Networks::class, 'network_id', 'network_id');
    
    }

    /**
     * This function defines the relationship to the Extensions model.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<Extensions, Devices, \Illuminate\Database\Eloquent\Relations\Pivot>
     */
    public function extensions()
    {
        // Many-to-Many relationship with Extensions.
        return $this->belongsToMany(Extensions::class, 'device_extensions', 'device_id', 'extension_id')
        ->withTimestamps();
    }
}
