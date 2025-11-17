<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceActivity extends Model
{
    protected $table = 'device_activity';
    protected $primaryKey = 'activity_id';
    
    protected $fillable = [
        'device_id',
        'activity_date',
        'day_number',
        'samples',
    ];

    protected $casts = [
        'samples' => 'array',
        'activity_date' => 'date',
    ];

    /**
     * Get the device that owns this activity record
     */
    public function device()
    {
        return $this->belongsTo(Devices::class, 'device_id', 'device_id');
    }

    /**
     * Get activity status at a specific sample index (0-287)
     */
    public function getSampleStatus(int $index): int
    {
        return $this->samples[$index] ?? 0;
    }

    /**
     * Set activity status at a specific sample index (0-287)
     */
    public function setSampleStatus(int $index, int $status): void
    {
        $samples = $this->samples ?? array_fill(0, 288, 0);
        $samples[$index] = $status;
        $this->samples = $samples;
    }
}
