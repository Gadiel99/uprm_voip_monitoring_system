<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Extensions extends Model
{
    protected $table = 'extensions';
    protected $guarded = [];
   
    protected $primaryKey = 'extension_number';
    public $incrementing = false;
    protected $keyType = 'string';
}
