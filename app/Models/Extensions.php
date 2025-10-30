<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Extensions extends Model
{
    // Table associated with the model.
    protected $table = 'extensions';

    // Primary key for the extensions table.
    protected $primaryKey = 'extension_id';

    // Mass assignable attributes.
    protected $guarded = [];

    /**
     * This function defines the relationship to the Devices model.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<Devices, Extensions, \Illuminate\Database\Eloquent\Relations\Pivot>
     */
    public function devices()
    {
        // Many-to-Many relationship with Devices.
        return $this->belongsToMany(Devices::class, 'device_extensions', 'extension_id', 'device_id')
        ->withTimestamps();
    }
}
