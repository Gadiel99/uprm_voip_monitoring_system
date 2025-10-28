<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Extensions extends Model
{
    protected $table = 'extensions';
    protected $guarded = [];
    public function devices()
    {

        return $this->belongsToMany(Devices::class, 'device_extensions', 'extension_id', 'device_id')
        ->withTimestamps();
    }
}
